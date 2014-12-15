<?php namespace Maatwebsite\Excel\Readers;

use PHPExcel;
use DOMNode;
use DOMText;
use DOMElement;
use DOMDocument;
use PHPExcel_Cell;
use PHPExcel_Settings;
use PHPExcel_Reader_HTML;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Font;
use PHPExcel_Style_Border;
use PHPExcel_Worksheet_Drawing;
use PHPExcel_Style_Alignment;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Parsers\CssParser;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;

/**
 *
 * LaravelExcel HTML reader
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @copyright  Original Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class Html extends PHPExcel_Reader_HTML {

    /**
     * Style per range
     * @var array
     */
    protected $styles = array();

    /**
     * Input encoding
     * @var string
     */
    protected $_inputEncoding = 'ANSI';

    /**
     * Sheet index to read
     * @var int
     */
    protected $_sheetIndex = 0;

    /**
     * HTML tags formatting settings
     * @var array
     */
    protected $_formats = array();

    /**
     * The current colspan
     * @var integer
     */
    protected $spanWidth = 1;

    /**
     * The current rowspan
     * @var integer
     */
    protected $spanHeight = 1;

    /**
     * @var
     */
    private $cssParser;

    /**
     * @param CssParser $cssParser
     */
    public function __construct(CssParser $cssParser)
    {
        $this->cssParser = $cssParser;
        parent::__construct();
    }

    /**
     * Loads PHPExcel from file
     *
     * @param   string                                 $pFilename
     * @param   boolean                                $isString
     * @param bool|LaravelExcelWorksheet|null|PHPExcel $obj
     * @throws \PHPExcel_Reader_Exception
     * @return  LaravelExcelWorksheet
     */
    public function load($pFilename, $isString = false, $obj = false)
    {
        // Set the default style formats
        $this->setStyleFormats();

        if ($obj instanceof PHPExcel)
        {
            // Load into this instance
            return $this->loadIntoExisting($pFilename, $obj, $isString);
        }
        elseif ($obj instanceof LaravelExcelWorksheet)
        {
            // Load into this instance
            return $this->loadIntoExistingSheet($pFilename, $obj, $isString);
        }

        $objPHPExcel = $obj ? $obj : new PHPExcel();

        return $this->loadIntoExisting($pFilename, $objPHPExcel, $isString);
    }

    /**
     * Set the style formats from our config file
     * @return  array
     */
    protected function setStyleFormats()
    {
        $this->_formats = Config::get('excel::views.styles', array());
    }

    /**
     * Loads HTML from file into sheet instance
     *
     * @param   string                $pFilename
     * @param   LaravelExcelWorksheet $sheet
     * @param   boolean               $isString
     * @return  LaravelExcelWorksheet
     * @throws  PHPExcel_Reader_Exception
     */
    public function loadIntoExistingSheet($pFilename, LaravelExcelWorksheet $sheet, $isString = false)
    {
        $isHtmlFile = false;

        // Check if it's a string or file
        if (!$isString)
        {
            // Double check if it's a file
            if (is_file($pFilename))
            {
                $isHtmlFile = true;
                $this->_openFile($pFilename);

                if (!$this->_isValidFormat())
                {
                    fclose($this->_fileHandle);
                    throw new PHPExcel_Reader_Exception($pFilename . " is an Invalid HTML file.");
                }

                fclose($this->_fileHandle);
            }
        }

        //  Create a new DOM object
        $dom = new DOMDocument;

        // Check if we need to load the file or the HTML
        if ($isHtmlFile)
        {
            // Load HTML from file
            if ((version_compare(PHP_VERSION, '5.4.0') >= 0) && defined(LIBXML_DTDLOAD))
            {
                $loaded = @$dom->loadHTMLFile($pFilename, PHPExcel_Settings::getLibXmlLoaderOptions());
            }
            else
            {
                $loaded = @$dom->loadHTMLFile($pFilename);
            }
        }
        else
        {
            // Load HTML from string
            @$dom->loadHTML(mb_convert_encoding($pFilename, 'HTML-ENTITIES', 'UTF-8'));

            // Let the css parser find all stylesheets
            $this->cssParser->findStyleSheets($dom);

            // Transform the css files to inline css and replace the html
            $html = $this->cssParser->transformCssToInlineStyles($pFilename);

            // Re-init dom doc
            $dom = new DOMDocument;

            // Load again with css included
            $loaded = @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        }

        if ($loaded === false)
        {
            throw new PHPExcel_Reader_Exception('Failed to load ' . $pFilename . ' as a DOM Document');
        }

        //  Discard white space
        $dom->preserveWhiteSpace = true;

        $row = 0;
        $column = 'A';
        $content = '';
        $this->_processDomElement($dom, $sheet, $row, $column, $content);

        if (!$sheet->hasFixedSizeColumns())
            $this->autosizeColumn($sheet);

        return $sheet;
    }

    /**
     * Autosize column for document
     * @param  LaravelExcelWorksheet $sheet
     * @return LaravelExcelWorksheet
     */
    public function autosizeColumn($sheet)
    {
        if ($columns = $sheet->getAutosize())
        {
            if (is_array($columns))
            {
                $sheet->setAutoSize($columns);
            }
            else
            {
                $toCol = $sheet->getHighestColumn();
                $toCol++;
                for ($i = 'A'; $i !== $toCol; $i++)
                {
                    $sheet->getColumnDimension($i)->setAutoSize(true);
                }

                $sheet->calculateColumnWidths();
            }
        }

        return $sheet;
    }

    /**
     * Process the dom element
     * @param  DOMNode               $element
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $row
     * @param  integer               $column
     * @param  string                $cellContent
     * @return void
     */
    protected function _processDomElement(DOMNode $element, $sheet, &$row, &$column, &$cellContent, $format = null)
    {
        foreach ($element->childNodes as $child)
        {
            // If is text
            if ($child instanceof DOMText)
            {
                // get the dom text
                $domText = preg_replace('/\s+/u', ' ', trim($child->nodeValue));

                //  simply append the text if the cell content is a plain text string
                if (is_string($cellContent))
                {
                    $cellContent .= $domText;
                }
            }

            // If is a dom element
            elseif ($child instanceof DOMElement)
            {
                $attributeArray = array();

                // Set row (=parent) styles
                if (isset($this->styles[$row]))
                    $this->parseInlineStyles($sheet, $column, $row, $this->styles[$row]);

                // Loop through the child's attributes
                foreach ($child->attributes as $attribute)
                {
                    // Add the attribute to the array
                    $attributeArray[$attribute->name] = $attribute->value;

                    // Attribute names
                    switch ($attribute->name)
                    {
                        // Colspan
                        case 'width':
                            $this->parseWidth($sheet, $column, $row, $attribute->value);
                            break;

                        case 'height':
                            $this->parseHeight($sheet, $column, $row, $attribute->value);
                            break;

                        // Colspan
                        case 'colspan':
                            $this->parseColSpan($sheet, $column, $row, $attribute->value, $child->attributes);
                            break;

                        // Rowspan
                        case 'rowspan':
                            $this->parseRowSpan($sheet, $column, $row, $attribute->value, $child->attributes);
                            break;

                        // Alignment
                        case 'align':
                            $this->parseAlign($sheet, $column, $row, $attribute->value);
                            break;

                        // Vertical alignment
                        case 'valign':
                            $this->parseValign($sheet, $column, $row, $attribute->value);
                            break;

                        // Inline css styles
                        case 'style':
                            $this->parseInlineStyles($sheet, $column, $row, $attribute->value);

                            if ($child->nodeName == 'tr')
                                $this->styles[$row] = $attribute->value;
                            break;
                    }
                }

                // nodeName
                switch ($child->nodeName)
                {

                    // Meta tags
                    case 'meta' :

                        // Loop through the attributes
                        foreach ($attributeArray as $attributeName => $attributeValue)
                        {

                            // Switch the names
                            switch ($attributeName)
                            {
                                // Set input encoding
                                case 'charset':
                                    $_inputEncoding = $attributeValue;
                                    break;
                            }
                        }

                        // Continue processing dom element
                        $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);

                        break;

                    // Set sheet title
                    case 'title' :
                        $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);
                        $sheet->setTitle($cellContent);
                        $cellContent = '';
                        break;

                    // Text
                    case 'span'  :
                    case 'div'   :
                    case 'font'  :
                    case 'i'     :
                    case 'em'    :
                    case 'strong':
                    case 'b'     :

                        // Add space after empty cells
                        if ($cellContent > '')
                            $cellContent .= ' ';

                        // Continue processing
                        $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);

                        // Add space after empty cells
                        if ($cellContent > '')
                            $cellContent .= ' ';

                        // Set the styling
                        if (isset($this->_formats[$child->nodeName]))
                        {
                            $sheet->getStyle($column . $row)
                                ->applyFromArray($this->_formats[$child->nodeName]);
                        }

                        break;

                    // Horizontal rules
                    case 'hr' :

                        // Flush the cell
                        $this->flushCell($sheet, $column, $row, $cellContent);

                        // count
                        ++$row;

                        // Set the styling
                        if (isset($this->_formats[$child->nodeName]))
                        {
                            $sheet->getStyle($column . $row)->applyFromArray($this->_formats[$child->nodeName]);
                        }
                        // If not, enter cell content
                        else
                        {
                            $cellContent = '----------';
                            $this->flushCell($sheet, $column, $row, $cellContent);
                        }

                        ++$row;

                    // Linebreaks
                    case 'br' :

                        // Add linebreak
                        if ($this->_tableLevel > 0)
                        {
                            $cellContent .= "\n";
                        }

                        //  Otherwise flush our existing content and move the row cursor on
                        else
                        {
                            $this->flushCell($sheet, $column, $row, $cellContent);
                            ++$row;
                        }

                        break;

                    // Hyperlinks
                    case 'a'  :

                        foreach ($attributeArray as $attributeName => $attributeValue)
                        {
                            switch ($attributeName)
                            {
                                case 'href':

                                    // Set the url
                                    $sheet->getCell($column . $row)
                                        ->getHyperlink()
                                        ->setUrl($attributeValue);

                                    // Set styling
                                    if (isset($this->_formats[$child->nodeName]))
                                    {
                                        $sheet->getStyle($column . $row)->applyFromArray($this->_formats[$child->nodeName]);
                                    }

                                    break;
                            }
                        }

                        // Add empty space
                        $cellContent .= ' ';
                        $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);

                        break;

                    // Headings/paragraphs and lists
                    case 'h1' :
                    case 'h2' :
                    case 'h3' :
                    case 'h4' :
                    case 'h5' :
                    case 'h6' :
                    case 'ol' :
                    case 'ul' :
                    case 'p'  :

                        if ($this->_tableLevel > 0)
                        {
                            $cellContent .= "\n";
                            $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);
                            $this->flushCell($sheet, $column, $row, $cellContent);

                            // Set style
                            if (isset($this->_formats[$child->nodeName]))
                            {
                                $sheet->getStyle($column . $row)->applyFromArray($this->_formats[$child->nodeName]);
                            }
                        }
                        else
                        {
                            if ($cellContent > '')
                            {
                                $this->flushCell($sheet, $column, $row, $cellContent);
                                $row += 2;
                            }
                            $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);
                            $this->flushCell($sheet, $column, $row, $cellContent);

                            // Set style
                            if (isset($this->_formats[$child->nodeName]))
                            {
                                $sheet->getStyle($column . $row)->applyFromArray($this->_formats[$child->nodeName]);
                            }

                            $row += 2;
                            $column = 'A';
                        }
                        break;

                    // List istem
                    case 'li'  :

                        if ($this->_tableLevel > 0)
                        {
                            //  If we're inside a table, replace with a \n
                            $cellContent .= "\n";
                            $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);
                        }
                        else
                        {
                            if ($cellContent > '')
                            {
                                $this->flushCell($sheet, $column, $row, $cellContent);
                            }

                            ++$row;

                            $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);
                            $this->flushCell($sheet, $column, $row, $cellContent);
                            $column = 'A';
                        }
                        break;

                    // Tables
                    case 'table' :

                        // Flush the cells
                        $this->flushCell($sheet, $column, $row, $cellContent);

                        // Set the start column
                        $column = $this->_setTableStartColumn($column);

                        if ($this->_tableLevel > 1)
                            --$row;

                        $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);

                        // Release the table start column
                        $column = $this->_releaseTableStartColumn();

                        if ($this->_tableLevel > 1)
                        {
                            ++$column;
                        }
                        else
                        {
                            ++$row;
                        }

                        break;

                    // Heading and body
                    case 'thead' :
                    case 'tbody' :
                        $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);
                        break;

                    case 'img':
                        $this->insertImageBySrc($sheet, $column, $row, $child);
                        break;

                    // Table rows
                    case 'tr' :

                        // Get start column
                        $column = $this->_getTableStartColumn();

                        // Set empty cell content
                        $cellContent = '';

                        // Continue processing
                        $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);

                        ++$row;

                        // reset the span height
                        $this->spanHeight = 1;

                        break;

                    // Table heading
                    case 'th' :
                        // Continue processing
                        $this->_processHeadings($child, $sheet, $row, $column, $cellContent);

                        // If we have a colspan, count the right amount of columns, else just 1
                        for ($w = 0; $w < $this->spanWidth; $w++)
                        {
                            ++$column;
                        }

                        // reset the span width after the process
                        $this->spanWidth = 1;

                        break;

                    // Table cell
                    case 'td' :
                        $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);
                        $this->flushCell($sheet, $column, $row, $cellContent);

                        // If we have a colspan, count the right amount of columns, else just 1
                        for ($w = 0; $w < $this->spanWidth; $w++)
                        {
                            ++$column;
                        }

                        // reset the span width after the process
                        $this->spanWidth = 1;
                        break;

                    // Html Body
                    case 'body' :
                        $row = 1;
                        $column = 'A';
                        $content = '';
                        $this->_tableLevel = 0;
                        $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);
                        break;

                    // Default
                    default:
                        $this->_processDomElement($child, $sheet, $row, $column, $cellContent, $format);
                }
            }
        }
    }

    /**
     * Set the start column
     * @param   string $column
     * @return  string
     */
    protected function _setTableStartColumn($column)
    {
        // Set to a
        if ($this->_tableLevel == 0)
            $column = 'A';

        ++$this->_tableLevel;

        // Get nested column
        $this->_nestedColumn[$this->_tableLevel] = $column;

        return $this->_nestedColumn[$this->_tableLevel];
    }

    /**
     * Get the table start column
     * @return string
     */
    protected function _getTableStartColumn()
    {
        return $this->_nestedColumn[$this->_tableLevel];
    }

    /**
     * Release the table start column
     * @return array
     */
    protected function _releaseTableStartColumn()
    {
        --$this->_tableLevel;

        return array_pop($this->_nestedColumn);
    }

    /**
     * Flush the cells
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param  string                $cellContent
     * @return void
     */
    protected function flushCell($sheet, &$column, $row, &$cellContent)
    {
        // Process merged cells
        list($column, $cellContent) = $this->processMergedCells($sheet, $column, $row, $cellContent);

        if (is_string($cellContent))
        {
            //  Simple String content
            if (trim($cellContent) > '')
            {
                //  Only actually write it if there's content in the string
                //  Write to worksheet to be done here...
                //  ... we return the cell so we can mess about with styles more easily

                $cell = $sheet->setCellValue($column . $row, $cellContent, true);
                $this->_dataArray[$row][$column] = $cellContent;
            }
        }
        else
        {
            $this->_dataArray[$row][$column] = 'RICH TEXT: ' . $cellContent;
        }
        $cellContent = (string) '';
    }

    /**
     * Process table headings
     * @param  string                $child
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $row
     * @param  integer               $column
     * @param                        $cellContent
     * @throws \PHPExcel_Exception
     * @return LaravelExcelWorksheet
     */
    protected function _processHeadings($child, $sheet, $row, $column, $cellContent)
    {
        $this->_processDomElement($child, $sheet, $row, $column, $cellContent);
        $this->flushCell($sheet, $column, $row, $cellContent);

        if (isset($this->_formats[$child->nodeName]))
        {
            $sheet->getStyle($column . $row)->applyFromArray($this->_formats[$child->nodeName]);
        }

        return $sheet;
    }

    /**
     * Insert a image inside the sheet
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param  string                $attributes
     * @return void
     */
    protected function insertImageBySrc($sheet, $column, $row, $attributes)
    {
        // Get attributes
        $src = $attributes->getAttribute('src');
        $width = (float) $attributes->getAttribute('width');
        $height = (float) $attributes->getAttribute('height');
        $alt = $attributes->getAttribute('alt');

        // init drawing
        $drawing = new PHPExcel_Worksheet_Drawing();

        // Set image
        $drawing->setPath($src);
        $drawing->setName($alt);
        $drawing->setWorksheet($sheet);
        $drawing->setCoordinates($column . $row);
        $drawing->setResizeProportional();

        // Set height and width
        if ($width > 0)
            $drawing->setWidth($width);

        if ($height > 0)
            $drawing->setHeight($height);

        // Set cell width based on image
        $this->parseWidth($sheet, $column, $row, $drawing->getWidth());
        $this->parseHeight($sheet, $column, $row, $drawing->getHeight());
    }

    /**
     * Set column width
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param  integer               $width
     * @return void
     */
    protected function parseWidth($sheet, $column, $row, $width)
    {
        $sheet->setWidth($column, $width);
    }

    /**
     * Set row height
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param  integer               $height
     * @return void
     */
    protected function parseHeight($sheet, $column, $row, $height)
    {
        $sheet->setHeight($row, $height);
    }

    /**
     * Parse colspans
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param  integer               $spanWidth
     * @param                        $attributes
     * @return void
     */
    protected function parseColSpan($sheet, $column, $row, $spanWidth, $attributes)
    {
        $startCell = $column . $row;

        $this->spanWidth = $spanWidth;

        // Find end column letter
        for ($i = 0; $i < ($spanWidth - 1); $i++)
        {
            ++$column;
        }

        // Set endcell
        $endCell = ($column) . $row;

        // Set range
        $range = $startCell . ':' . $endCell;

        // Remember css inline styles
        foreach ($attributes as $attribute)
        {
            if ($attribute->name == 'style')
            {
                $this->styles[$range] = $attribute->value;
            }
        }

        // Merge the cells
        $sheet->mergeCells($range);
    }

    /**
     * Parse colspans
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param  integer               $spanHeight
     * @param                        $attributes
     * @return void
     */
    protected function parseRowSpan($sheet, $column, $row, $spanHeight, $attributes)
    {
        // Set the span height
        $this->spanHeight = --$spanHeight;

        // Set start cell
        $startCell = $column . $row;

        // Set endcell = current row number + spanheight
        $endCell = $column . ($row + $this->spanHeight);
        $range = $startCell . ':' . $endCell;

        // Remember css inline styles
        //foreach($attributes as $attribute)
        //{
        //    if($attribute->name == 'style')
        //    {
        //        $this->styles[$range] = $attribute->value;
        //    }
        //}

        // Merge the cells
        $sheet->mergeCells($range);
    }

    /**
     * Parse the align
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param  string                $value
     * @return void
     */
    protected function parseAlign($sheet, $column, $row, $value)
    {

        $horizontal = false;
        $cells = $sheet->getStyle($column . $row);

        switch ($value)
        {
            case 'center':
                $horizontal = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                break;

            case 'left':
                $horizontal = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
                break;

            case 'right':
                $horizontal = PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
                break;

            case 'justify':
                $horizontal = PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY;
                break;
        }

        if ($horizontal)
            $cells->getAlignment()->applyFromArray(
                array('horizontal' => $horizontal)
            );
    }

    /**
     * Parse the valign
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param  string                $value
     * @return void
     */
    protected function parseValign($sheet, $column, $row, $value)
    {

        $vertical = false;
        $cells = $sheet->getStyle($column . $row);

        switch ($value)
        {
            case 'top':
                $vertical = PHPExcel_Style_Alignment::VERTICAL_TOP;
                break;

            case 'middle':
                $vertical = PHPExcel_Style_Alignment::VERTICAL_CENTER;
                break;

            case 'bottom':
                $vertical = PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
                break;

            case 'justify':
                $vertical = PHPExcel_Style_Alignment::VERTICAL_JUSTIFY;
                break;
        }

        if ($vertical)
            $cells->getAlignment()->applyFromArray(
                array('vertical' => $vertical)
            );
    }

    /**
     * Parse the inline styles
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param  string                $styleTag
     * @return void
     */
    protected function parseInlineStyles($sheet, $column, $row, $styleTag)
    {
        // Seperate the different styles
        $styles = explode(';', $styleTag);

        $this->parseCssAttributes($sheet, $column, $row, $styles);
    }

    /**
     * Parse the styles
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param                        array @styles
     * @return void
     */
    protected function parseCssAttributes($sheet, $column, $row, $styles = array())
    {
        foreach ($styles as $tag)
        {
            $style = explode(':', $tag);
            $name = trim(reset($style));
            $value = trim(end($style));

            $this->parseCssProperties($sheet, $column, $row, $name, $value);
        }
    }

    /**
     * Parse CSS
     * @param  LaravelExcelWorksheet $sheet
     * @param  string                $column
     * @param  integer               $row
     * @param  string                $name
     * @param  string                $value
     * @return void
     */
    protected function parseCssProperties($sheet, $column, $row, $name, $value)
    {
        $cells = $sheet->getStyle($column . $row);
        switch ($name)
        {
            // Cell width
            case 'width':
                $this->parseWidth($sheet, $column, $row, $value);
                break;

            // Row height
            case 'height':
                $this->parseHeight($sheet, $column, $row, $value);
                break;

            // BACKGROUND
            case 'background':
            case 'background-color':

                $original = $value;

                $value = $this->getColor($value);

                $cells->getFill()->applyFromArray(
                    array(
                        'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $value)
                    )
                );

                break;

            // TEXT COLOR
            case 'color':
                $value = $this->getColor($value);
                $cells->getFont()->getColor()->applyFromArray(
                    array('rgb' => $value)
                );
                break;

            // FONT SIZE
            case 'font-size':
                $cells->getFont()->setSize($value);
                break;

            // FONT WEIGHT
            case 'font-weight':
                if ($value == 'bold' || $value >= 500)
                    $cells->getFont()->setBold(true);
                break;

            // FONT STYLE
            case 'font-style':
                if ($value == 'italic')
                    $cells->getFont()->setItalic(true);
                break;

            // FONT FACE
            case 'font-family':
                $cells->getFont()->applyFromArray(
                    array('name' => $value)
                );
                break;

            // TEXT DECORATION
            case 'text-decoration':
                switch ($value)
                {
                    case 'underline':
                        $cells->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                        break;

                    case 'line-through':
                        $cells->getFont()->setStrikethrough(true);
                        break;
                }
                break;

            // Text align
            case 'text-align':

                $horizontal = false;

                switch ($value)
                {
                    case 'center':
                        $horizontal = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                        break;

                    case 'left':
                        $horizontal = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
                        break;

                    case 'right':
                        $horizontal = PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
                        break;

                    case 'justify':
                        $horizontal = PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY;
                        break;
                }

                if ($horizontal)
                    $cells->getAlignment()->applyFromArray(
                        array('horizontal' => $horizontal)
                    );

                break;

            // Vertical align
            case 'vertical-align':

                $vertical = false;

                switch ($value)
                {
                    case 'top':
                        $vertical = PHPExcel_Style_Alignment::VERTICAL_TOP;
                        break;

                    case 'middle':
                        $vertical = PHPExcel_Style_Alignment::VERTICAL_CENTER;
                        break;

                    case 'bottom':
                        $vertical = PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
                        break;

                    case 'justify':
                        $vertical = PHPExcel_Style_Alignment::VERTICAL_JUSTIFY;
                        break;
                }

                if ($vertical)
                    $cells->getAlignment()->applyFromArray(
                        array('vertical' => $vertical)
                    );
                break;

            // Borders
            case 'border':
            case 'borders':
                $borders = explode(' ', $value);
                $style = $borders[1];
                $color = end($borders);
                $color = $this->getColor($color);
                $borderStyle = $this->borderStyle($style);

                $cells->getBorders()->applyFromArray(
                    array('allborders' => array('style' => $borderStyle, 'color' => array('rgb' => $color)))
                );
                break;

            // Border-top
            case 'border-top':
                $borders = explode(' ', $value);
                $style = $borders[1];
                $color = end($borders);
                $color = $this->getColor($color);

                $borderStyle = $this->borderStyle($style);

                $cells->getBorders()->getTop()->applyFromArray(
                    array('style' => $borderStyle, 'color' => array('rgb' => $color))
                );
                break;

            // Border-bottom
            case 'border-bottom':
                $borders = explode(' ', $value);
                $style = $borders[1];
                $color = end($borders);
                $color = $this->getColor($color);
                $borderStyle = $this->borderStyle($style);

                $cells->getBorders()->getBottom()->applyFromArray(
                    array('style' => $borderStyle, 'color' => array('rgb' => $color))
                );
                break;

            // Border-right
            case 'border-right':
                $borders = explode(' ', $value);
                $style = $borders[1];
                $color = end($borders);
                $color = $this->getColor($color);
                $borderStyle = $this->borderStyle($style);

                $cells->getBorders()->getRight()->applyFromArray(
                    array('style' => $borderStyle, 'color' => array('rgb' => $color))
                );
                break;

            // Border-left
            case 'border-left':
                $borders = explode(' ', $value);
                $style = $borders[1];
                $color = end($borders);
                $color = $this->getColor($color);
                $borderStyle = $this->borderStyle($style);

                $cells->getBorders()->getLeft()->applyFromArray(
                    array('style' => $borderStyle, 'color' => array('rgb' => $color))
                );
                break;

            // wrap-text
            case 'wrap-text':

                if ($value == 'true')
                    $wrap = true;

                if (!$value || $value == 'false')
                    $wrap = false;

                $cells->getAlignment()->setWrapText($wrap);

                break;
        }
    }

    /**
     * Get the color
     * @param  string $color
     * @return string
     */
    public function getColor($color)
    {
        $color = str_replace('#', '', $color);

        // If color is only 3 chars long, mirror it to 6 chars
        if (strlen($color) == 3)
            $color = $color . $color;

        return $color;
    }

    /**
     * Get the border style
     * @param  string $style
     * @return string
     */
    public function borderStyle($style)
    {
        switch ($style)
        {
            case 'solid';
                return PHPExcel_Style_Border::BORDER_THIN;
                break;

            case 'dashed':
                return PHPExcel_Style_Border::BORDER_DASHED;
                break;

            case 'dotted':
                return PHPExcel_Style_Border::BORDER_DOTTED;
                break;

            case 'medium':
                return PHPExcel_Style_Border::BORDER_MEDIUM;
                break;

            case 'thick':
                return PHPExcel_Style_Border::BORDER_THICK;
                break;

            case 'none':
                return PHPExcel_Style_Border::BORDER_NONE;
                break;

            case 'dash-dot':
                return PHPExcel_Style_Border::BORDER_DASHDOT;
                break;

            case 'dash-dot-dot':
                return PHPExcel_Style_Border::BORDER_DASHDOTDOT;
                break;

            case 'double':
                return PHPExcel_Style_Border::BORDER_DOUBLE;
                break;

            case 'hair':
                return PHPExcel_Style_Border::BORDER_HAIR;
                break;

            case 'medium-dash-dot':
                return PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT;
                break;

            case 'medium-dash-dot-dot':
                return PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT;
                break;

            case 'medium-dashed':
                return PHPExcel_Style_Border::BORDER_MEDIUMDASHED;
                break;

            case 'slant-dash-dot':
                return PHPExcel_Style_Border::BORDER_SLANTDASHDOT;
                break;

            default:
                return '';
                break;
        }
    }

    /**
     * @param $sheet
     * @param $column
     * @param $row
     * @param $cellContent
     * @throws \PHPExcel_Exception
     * @return array
     */
    private function processMergedCells($sheet, &$column, $row, $cellContent)
    {
        // Find the cells
        $cell = $sheet->getCell($column . $row);

        // Get the merged cells
        foreach ($sheet->getMergeCells() as $mergedCells)
        {
            // If cells is in the merged cells range
            if ($cell->isInRange($mergedCells))
            {
                // Get columns
                preg_match("/(.*):(.*?)/u", $mergedCells, $matches);

                // skip the first item in the merge
                if ($matches[1] != $column . $row)
                {
                    $newCol = PHPExcel_Cell::stringFromColumnIndex(
                        (PHPExcel_Cell::columnIndexFromString($column) + 1) - 1
                    );

                    $column = $newCol;

                    // Set style for merged cells
                    if (isset($this->styles[$row]))
                        $this->parseInlineStyles($sheet, $column, $row, $this->styles[$row]);

                    // Flush cell
                    $this->flushCell($sheet, $column, $row, $cellContent);
                }
            }
        }

        return array($column, $cellContent);
    }
}