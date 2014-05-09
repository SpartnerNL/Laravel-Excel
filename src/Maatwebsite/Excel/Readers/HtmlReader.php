<?php

/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2013 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Reader
 * @copyright  Copyright (c) 2006 - 2013 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    ##VERSION##, ##DATE##
 */

namespace Maatwebsite\Excel\Readers;

use \PHPExcel;
use \PHPExcel_Reader_HTML;
use \PHPExcel_Style_Color;
use \PHPExcel_Style_Border;
use \PHPExcel_Style_Fill;
use \PHPExcel_Style_Font;
use \PHPExcel_Style_Alignment;
use Maatwebsite\Excel\Parsers\CssParser;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;

class Html extends \PHPExcel_Reader_HTML
{

    /**
     * Input encoding
     *
     * @var string
     */
    private $_inputEncoding = 'ANSI';

    /**
     * Sheet index to read
     *
     * @var int
     */
    private $_sheetIndex    = 0;

    /**
     * HTML tags formatting settings
     * @var array
     */
    private $_formats = array(
        'th' => array(
            'font' => array(
                'bold' => true,
                'size' => 12,
            ),
        ),
        'strong' => array(
            'font' => array(
                'bold' => true,
                'size' => 12,
            ),
        ),
        'b' => array(
            'font' => array(
                'bold' => true,
                'size' => 12,
            ),
        ),
        'i' => array(
            'font' => array(
                'italic' => true,
                'size' => 12,
            ),
        ),
        'h1' => array(
            'font' => array(
                'bold' => true,
                'size' => 24,
            ),
        ),  //  Bold, 24pt
       'h2' => array(
            'font' => array(
                'bold' => true,
                'size' => 18,
            ),
        ),  //  Bold, 18pt
       'h3' => array(
            'font' => array(
                'bold' => true,
                'size' => 13.5,
            ),
        ),  //  Bold, 13.5pt
       'h4' => array(
            'font' => array(
                'bold' => true,
                'size' => 12,
            ),
        ),  //  Bold, 12pt
       'h5' => array(
            'font' => array(
                'bold' => true,
                'size' => 10,
            ),
        ),  //  Bold, 10pt
       'h6' => array(
            'font' => array(
                'bold' => true,
                'size' => 7.5,
            ),
        ),  //  Bold, 7.5pt
       'a'  => array(
            'font' => array(
                'underline' => true,
                'color' => array( 'argb' => PHPExcel_Style_Color::COLOR_BLUE),
            ),
        ),  //  Blue underlined
       'hr' => array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(PHPExcel_Style_Color::COLOR_BLACK)
                ),
            ),
        ),  //  Bottom border
     );

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
     * Loads PHPExcel from file
     *
     * @param   string      $pFilename
     * @return  PHPExcel
     * @throws  PHPExcel_Reader_Exception
     */
    public function load($pFilename, $isString = false, $obj = false)
    {

        if($obj instanceof \PHPExcel)
        {
            // Load into this instance
            return $this->loadIntoExisting($pFilename, $obj, $isString);
        }
        elseif($obj instanceof LaravelExcelWorksheet)
        {
            // Load into this instance
            return $this->loadIntoExistingSheet($pFilename, $obj, $isString);
        }

        $objPHPExcel = $obj ? $obj : new \PHPExcel();
        return $this->loadIntoExisting($pFilename, $objPHPExcel, $isString);
    }

    /**
     * Loads HTML from file into sheet instance
     *
     * @param   string      $pFilename
     * @param   LaravelExcelWorksheet    $sheet
     * @return  PHPExcel
     * @throws  PHPExcel_Reader_Exception
     */
    public function loadIntoExistingSheet($pFilename, LaravelExcelWorksheet $sheet, $isString = false)
    {

        $isHtmlFile = FALSE;

        // Check if it's a string or file
        if(!$isString)
        {
            // Double check if it's a file
            if(is_file($pFilename)){
               $isHtmlFile = TRUE;
               // Open file to validate
               $this->_openFile($pFilename);

               if (!$this->_isValidFormat()) {
                 fclose ($this->_fileHandle);
                 throw new PHPExcel_Reader_Exception($pFilename . " is an Invalid HTML file.");
               }

                fclose ($this->_fileHandle);
            }
        }

        //  Create a new DOM object
        $dom = new \domDocument;

        // Check if we need to load the file or the HTML
        if($isHtmlFile)
        {
            // Load HTML from file
            $loaded = @$dom->loadHTMLFile($pFilename);
        }
        else
        {
            // Load HTML from string
            $loaded = @$dom->loadHTML(mb_convert_encoding($pFilename, 'HTML-ENTITIES', 'UTF-8'));
        }

        if ($loaded === FALSE) {
            throw new \PHPExcel_Reader_Exception('Failed to load ',$pFilename,' as a DOM Document');
        }

        // Parse css
        $this->css = new CssParser($dom);

        //  Discard white space
        $dom->preserveWhiteSpace = true;

        $row = 0;
        $column = 'A';
        $content = '';
        $this->_processDomElement($dom,$sheet,$row,$column,$content);
        $this->autosizeColumn($sheet);

        return $sheet;
    }

    /**
     * Autosize column for document
     *
     * @return int
     */
    public function autosizeColumn($sheet)
    {
        if($columns = $sheet->getAutosize())
        {
            if(is_array($columns))
            {
                $sheet->setAutoSize($columns);
            }
            else
            {
                $toCol = $sheet->getHighestColumn();

                $toCol++;
                for ($i = 'A'; $i !== $toCol; $i++) {
                    $sheet->getColumnDimension($i)->setAutoSize(true);
                }

                $sheet->calculateColumnWidths();
            }
        }

        return $sheet;
    }

    private function _processDomElement(\DOMNode $element, $sheet, &$row, &$column, &$cellContent){

        foreach($element->childNodes as $child){

            // set the spannend column size
            //$this->spanWidth = 1;

            if ($child instanceof \DOMText) {
                $domText = preg_replace('/\s+/u',' ',trim($child->nodeValue));
                if (is_string($cellContent)) {
                    //  simply append the text if the cell content is a plain text string
                    $cellContent .= $domText;
                } else {
                    //  but if we have a rich text run instead, we need to append it correctly
                    //  TODO
                }
            } elseif($child instanceof \DOMElement) {
             // echo '<b>DOM ELEMENT: </b>' , strtoupper($child->nodeName) , '<br />';

                $attributeArray = array();
                foreach($child->attributes as $attribute) {
                 // echo '<b>ATTRIBUTE: </b>' , $attribute->name , ' => ' , $attribute->value , '<br />';
                    $attributeArray[$attribute->name] = $attribute->value;

                    // Attribute names
                    switch($attribute->name) {

                        case 'style':

                            // Pare style tags
                            $this->parseInlineStyles($sheet, $column, $row, $attribute->value);

                        break;

                        case 'colspan':
                            $this->parseColSpan($sheet, $column, $row, $attribute->value);
                            break;

                        case 'rowspan':
                            $this->parseRowSpan($sheet, $column, $row, $attribute->value);
                            break;

                        case 'align':
                            $this->parseAlign($sheet, $column, $row, $attribute->value);
                            break;

                        case 'valign':
                            $this->parseValign($sheet, $column, $row, $attribute->value);
                            break;

                        case 'class':
                            $this->styleByClass($sheet, $column, $row, $attribute->value);
                            break;

                        case 'id':
                            $this->styleById($sheet, $column, $row, $attribute->value);
                            break;

                    }


                }

                // nodeName
                switch($child->nodeName) {

                    case 'meta' :
                        foreach($attributeArray as $attributeName => $attributeValue) {
                            switch($attributeName) {
                                case 'content':
                                    //  TODO
                                    //  Extract character set, so we can convert to UTF-8 if required
                                    break;
                            }
                        }
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
                        break;
                    case 'title' :
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
                        $sheet->setTitle($cellContent);
                        $cellContent = '';
                        break;
                    case 'span'  :
                    case 'div'   :
                    case 'font'  :
                    case 'i'     :
                    case 'em'    :
                    case 'strong':
                    case 'b'     :

//                      echo 'STYLING, SPAN OR DIV<br />';
                        if ($cellContent > '')
                            $cellContent .= ' ';
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
                        if ($cellContent > '')
                            $cellContent .= ' ';

                        // Set the styling
                        if (isset($this->_formats[$child->nodeName])) {
                            $sheet->getStyle($column.$row)->applyFromArray($this->_formats[$child->nodeName]);
                        }

//                      echo 'END OF STYLING, SPAN OR DIV<br />';
                        break;
                    case 'hr' :
                        $this->_flushCell($sheet,$column,$row,$cellContent);
                        ++$row;
                        if (isset($this->_formats[$child->nodeName])) {
                            $sheet->getStyle($column.$row)->applyFromArray($this->_formats[$child->nodeName]);
                        } else {
                            $cellContent = '----------';
                            $this->_flushCell($sheet,$column,$row,$cellContent);
                        }
                        ++$row;
                    case 'br' :
                        if ($this->_tableLevel > 0) {
                            //  If we're inside a table, replace with a \n
                            $cellContent .= "\n";
                        } else {
                            //  Otherwise flush our existing content and move the row cursor on
                            $this->_flushCell($sheet,$column,$row,$cellContent);
                            ++$row;
                        }
//                      echo 'HARD LINE BREAK: ' , '<br />';
                        break;
                    case 'a'  :
//                      echo 'START OF HYPERLINK: ' , '<br />';
                        foreach($attributeArray as $attributeName => $attributeValue) {
                            switch($attributeName) {
                                case 'href':
//                                  echo 'Link to ' , $attributeValue , '<br />';
                                    $sheet->getCell($column.$row)->getHyperlink()->setUrl($attributeValue);

                                    if (isset($this->_formats[$child->nodeName])) {
                                        $sheet->getStyle($column.$row)->applyFromArray($this->_formats[$child->nodeName]);
                                    }
                                    break;
                            }
                        }
                        $cellContent .= ' ';
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
//                      echo 'END OF HYPERLINK:' , '<br />';
                        break;
                    case 'h1' :
                    case 'h2' :
                    case 'h3' :
                    case 'h4' :
                    case 'h5' :
                    case 'h6' :
                    case 'ol' :
                    case 'ul' :
                    case 'p'  :

                        if ($this->_tableLevel > 0) {
                            //  If we're inside a table, replace with a \n
                            $cellContent .= "\n";
//                          echo 'LIST ENTRY: ' , '<br />';
                            $this->_processDomElement($child,$sheet,$row,$column,$cellContent);

                            $this->_flushCell($sheet,$column,$row,$cellContent);

                            if (isset($this->_formats[$child->nodeName])) {
                                $sheet->getStyle($column.$row)->applyFromArray($this->_formats[$child->nodeName]);
                            }

//                          echo 'END OF LIST ENTRY:' , '<br />';
                        } else {
                            if ($cellContent > '') {
                                $this->_flushCell($sheet,$column,$row,$cellContent);
                                $row += 2;
                            }
//                          echo 'START OF PARAGRAPH: ' , '<br />';
                            $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
//                          echo 'END OF PARAGRAPH:' , '<br />';
                            $this->_flushCell($sheet,$column,$row,$cellContent);

                            if (isset($this->_formats[$child->nodeName])) {
                                $sheet->getStyle($column.$row)->applyFromArray($this->_formats[$child->nodeName]);
                            }

                            $row += 2;
                            $column = 'A';
                        }
                        break;
                    case 'li'  :
                        if ($this->_tableLevel > 0) {
                            //  If we're inside a table, replace with a \n
                            $cellContent .= "\n";
//                          echo 'LIST ENTRY: ' , '<br />';
                            $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
//                          echo 'END OF LIST ENTRY:' , '<br />';
                        } else {
                            if ($cellContent > '') {
                                $this->_flushCell($sheet,$column,$row,$cellContent);
                            }
                            ++$row;
//                          echo 'LIST ENTRY: ' , '<br />';
                            $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
//                          echo 'END OF LIST ENTRY:' , '<br />';
                            $this->_flushCell($sheet,$column,$row,$cellContent);
                            $column = 'A';
                        }
                        break;
                    case 'table' :
                        $this->_flushCell($sheet,$column,$row,$cellContent);
                        $column = $this->_setTableStartColumn($column);
//                      echo 'START OF TABLE LEVEL ' , $this->_tableLevel , '<br />';
                        if ($this->_tableLevel > 1)
                            --$row;
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
//                      echo 'END OF TABLE LEVEL ' , $this->_tableLevel , '<br />';
                        $column = $this->_releaseTableStartColumn();
                        if ($this->_tableLevel > 1) {
                            ++$column;
                        } else {
                            ++$row;
                        }
                        break;
                    case 'thead' :
                    case 'tbody' :
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
                        break;
                    case 'tr' :
                        $column = $this->_getTableStartColumn();
                        $cellContent = '';
//                      echo 'START OF TABLE ' , $this->_tableLevel , ' ROW<br />';
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
//                      echo 'END OF TABLE ' , $this->_tableLevel , ' ROW<br />';
//
//                      Count the rows after the element was parsed

                        // If we have a rowspan, count the right amount of rows, else just 1
                        for($i = 0; $i < $this->spanHeight; $i++)
                        {
                            ++$row;
                        }

                        // reset the span height after the process
                        $this->spanHeight = 1;

                        break;
                    case 'th' :
                        $this->_processHeadings($child, $sheet, $row, $column, $cellContent);
                        ++$column;
                        break;
                    case 'td' :
//                      echo 'START OF TABLE ' , $this->_tableLevel , ' CELL<br />';
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
//                      echo 'END OF TABLE ' , $this->_tableLevel , ' CELL<br />';
                        $this->_flushCell($sheet,$column,$row,$cellContent);

                        // If we have a colspan, count the right amount of columns, else just 1
                        for($i = 0; $i < $this->spanWidth; $i++)
                        {
                            ++$column;
                        }

                        // reset the span width after the process
                        $this->spanWidth = 1;

                        break;
                    case 'body' :
                        $row = 1;
                        $column = 'A';
                        $content = '';
                        $this->_tableLevel = 0;
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
                        break;
                    default:
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
                }
            }
        }
    }

    //  Data Array used for testing only, should write to PHPExcel object on completion of tests
    private $_dataArray = array();

    private $_tableLevel = 0;
    private $_nestedColumn = array('A');

    private function _setTableStartColumn($column) {
        if ($this->_tableLevel == 0)
            $column = 'A';
        ++$this->_tableLevel;
        $this->_nestedColumn[$this->_tableLevel] = $column;

        return $this->_nestedColumn[$this->_tableLevel];
    }

    private function _getTableStartColumn() {
        return $this->_nestedColumn[$this->_tableLevel];
    }

    private function _releaseTableStartColumn() {
        --$this->_tableLevel;
        return array_pop($this->_nestedColumn);
    }

    private function _flushCell($sheet,$column,$row,&$cellContent) {
        if (is_string($cellContent)) {
            //  Simple String content
            if (trim($cellContent) > '') {
                //  Only actually write it if there's content in the string
//              echo 'FLUSH CELL: ' , $column , $row , ' => ' , $cellContent , '<br />';
                //  Write to worksheet to be done here...
                //  ... we return the cell so we can mess about with styles more easily
                $cell = $sheet->setCellValue($column.$row,$cellContent,true);
                $this->_dataArray[$row][$column] = $cellContent;
            }
        } else {
            //  We have a Rich Text run
            //  TODO
            $this->_dataArray[$row][$column] = 'RICH TEXT: ' . $cellContent;
        }
        $cellContent = (string) '';
    }

    /**
     * Process table headings
     * @param  [type] $child  [description]
     * @param  [type] $sheet  [description]
     * @param  [type] $row    [description]
     * @param  [type] $column [description]
     * @return [type]         [description]
     */
    protected function _processHeadings($child, $sheet, $row, $column, $cellContent)
    {

        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
        $this->_flushCell($sheet,$column,$row,$cellContent);

        if (isset($this->_formats[$child->nodeName])) {
            $sheet->getStyle($column.$row)->applyFromArray($this->_formats[$child->nodeName]);
        }

        return $sheet;
    }

    /**
     * Style the element by class
     * @param  [type] $sheet  [description]
     * @param  [type] $column [description]
     * @param  [type] $row    [description]
     * @param  [type] $class  [description]
     * @return [type]         [description]
     */
    protected function styleByClass($sheet, $column, $row, $class)
    {
        // If the class has a whitespace
        // break into multiple classes
        if(str_contains($class, ' '))
        {
            $classes = explode(' ', $class);
            foreach($classes as $class)
            {
                return $this->styleByClass($sheet, $column, $row, $class);
            }
        }

        // Lookup the css
        $styles = $this->css->lookup('class', $class);

        // Loop through the styles
        foreach($styles as $name => $value)
        {
            $this->parseCssProperties($sheet, $column, $row, $name, $value);
        }
    }

    /**
     * Style the element by class
     * @param  [type] $sheet  [description]
     * @param  [type] $column [description]
     * @param  [type] $row    [description]
     * @param  [type] $class  [description]
     * @return [type]         [description]
     */
    protected function styleById($sheet, $column, $row, $class)
    {
        $styles = $this->css->lookup('id', $class);

        foreach($styles as $name => $value)
        {
            $this->parseCssProperties($sheet, $column, $row, $name, $value);
        }
    }

    /**
     * Parse colspans
     * @param  [type] $sheet  [description]
     * @param  [type] $column [description]
     * @param  [type] $row    [description]
     * @param  [type] $tag    [description]
     * @return [type]         [description]
     */
    protected function parseColSpan($sheet, $column, $row, $spanWidth)
    {
        $startCell = $column.$row;

        $this->spanWidth = $spanWidth;

        // Find end column letter
        for($i = 0; $i < ($spanWidth - 1); $i++)
        {
            ++$column;
        }

        // Set endcell
        $endCell = ($column).$row;

        // Set range
        $range = $startCell . ':' . $endCell;

        // Merge the cells
        $sheet->mergeCells($range);
    }

    /**
     * Parse colspans
     * @param  [type] $sheet  [description]
     * @param  [type] $column [description]
     * @param  [type] $row    [description]
     * @param  [type] $tag    [description]
     * @return [type]         [description]
     */
    protected function parseRowSpan($sheet, $column, $row, $spanHeight)
    {
        // Set the spanHeight
        $this->spanHeight = $spanHeight;

        $startCell = $column.$row;
        $endCell = $column.($row * $spanHeight);
        $range = $startCell . ':' . $endCell;

        $sheet->mergeCells($range);
    }

    /**
     * Parse the align
     * @param  [type] $sheet  [description]
     * @param  [type] $column [description]
     * @param  [type] $row    [description]
     * @param  [type] $value  [description]
     * @return [type]         [description]
     */
    protected function parseAlign($sheet, $column, $row, $value)
    {

        $horizontal = false;
        $cells = $sheet->getStyle($column.$row);

        switch($value)
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

        if($horizontal)
            $cells->getAlignment()->applyFromArray(
                array('horizontal' => $horizontal)
            );
    }

    /**
     * Parse the valign
     * @param  [type] $sheet  [description]
     * @param  [type] $column [description]
     * @param  [type] $row    [description]
     * @param  [type] $value  [description]
     * @return [type]         [description]
     */
    protected function parseValign($sheet, $column, $row, $value)
    {

        $vertical = false;
        $cells = $sheet->getStyle($column.$row);

        switch($value)
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

        if($vertical)
            $cells->getAlignment()->applyFromArray(
                array('vertical' => $vertical)
            );
    }

    /**
     * Parse the inline styles
     * @param  [type] $sheet    [description]
     * @param  [type] $column   [description]
     * @param  [type] $row      [description]
     * @param  [type] $styleTag [description]
     * @return [type]           [description]
     */
    protected function parseInlineStyles($sheet, $column, $row, $styleTag)
    {
        // Seperate the different styles
        $styles = explode(';', $styleTag);

        $this->parseCssAttributes($sheet, $column, $row, $styles);
    }

    /**
     * Parse the styles
     * @param  [type] $sheet  [description]
     * @param  [type] $column [description]
     * @param  [type] $row    [description]
     * @param  [type] $styles [description]
     * @return [type]         [description]
     */
    protected function parseCssAttributes($sheet, $column, $row, $styles = array())
    {
        foreach($styles as $tag)
        {
            $style = explode(':', $tag);
            $name = trim(reset($style));
            $value = trim(end($style));

            $this->parseCssProperties($sheet, $column, $row, $name, $value);
        }
    }

    /**
     * Parse CSS
     * @param  [type] $sheet  [description]
     * @param  [type] $column [description]
     * @param  [type] $row    [description]
     * @param  [type] $name   [description]
     * @param  [type] $value  [description]
     * @return [type]         [description]
     */
    protected function parseCssProperties($sheet, $column, $row, $name, $value)
    {
        $cells = $sheet->getStyle($column.$row);
        switch($name)
        {

            // BACKGROUND
            case 'background':
                $value = $this->getColor($value);

                $cells->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array ('rgb' => $value)
                        )
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
                if($value == 'bold' || $value >= 500)
                    $cells->getFont()->setBold(true);
                break;

            // FONT STYLE
            case 'font-style':
                if($value == 'italic')
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
                switch($value)
                {
                    case 'underline':
                        $cells->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                        break;

                    case 'line-through':
                        $cells->getFont()->setStrikethrough(true);
                        break;
                }
                break;

            case 'text-align':

                $horizontal = false;

                switch($value)
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

                if($horizontal)
                    $cells->getAlignment()->applyFromArray(
                        array('horizontal' => $horizontal)
                    );

                break;

            case 'vertical-align':

                $vertical = false;

                switch($value)
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

                if($vertical)
                    $cells->getAlignment()->applyFromArray(
                        array('vertical' => $vertical)
                    );
                break;

            case 'border':
            case 'borders':
                $borders = explode(' ', $value);
                $style = $borders[1];
                $color = end($borders);
                $color = $this->getColor($color);
                $borderStyle = $this->borderStyle($style);

                $cells->getBorders()->applyFromArray(
                    array( 'allborders' => array( 'style' => $borderStyle, 'color' => array( 'rgb' => $color ) ) )
                );
                break;

            case 'border-top':
                $borders = explode(' ', $value);
                $style = $borders[1];
                $color = end($borders);
                $color = $this->getColor($color);

                $borderStyle = $this->borderStyle($style);

                $cells->getBorders()->getTop()->applyFromArray(
                    array( 'style' => $borderStyle, 'color' => array( 'rgb' => $color ))
                );
                break;

            case 'border-bottom':
                $borders = explode(' ', $value);
                $style = $borders[1];
                $color = end($borders);
                $color = $this->getColor($color);
                $borderStyle = $this->borderStyle($style);

                $cells->getBorders()->getBottom()->applyFromArray(
                    array( 'style' => $borderStyle, 'color' => array( 'rgb' => $color ))
                );
                break;

            case 'border-right':
                $borders = explode(' ', $value);
                $style = $borders[1];
                $color = end($borders);
                $color = $this->getColor($color);
                $borderStyle = $this->borderStyle($style);

                $cells->getBorders()->getRight()->applyFromArray(
                    array( 'style' => $borderStyle, 'color' => array( 'rgb' => $color ))
                );
                break;

            case 'border-left':
                $borders = explode(' ', $value);
                $style = $borders[1];
                $color = end($borders);
                $color = $this->getColor($color);
                $borderStyle = $this->borderStyle($style);

                $cells->getBorders()->getLeft()->applyFromArray(
                    array( 'style' => $borderStyle, 'color' => array( 'rgb' => $color ))
                );
                break;

        }
    }

    /**
     * Get the color
     * @param  [type] $color [description]
     * @return [type]        [description]
     */
    public function getColor($color)
    {
        $color = str_replace('#', '', $color);

        // If color is only 3 chars long, mirror it to 6 chars
        if(strlen($color) == 3)
            $color = $color . $color;

        return $color;

    }

    public function borderStyle($style)
    {

        switch($style) {
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

            default:
                return '';
                break;
        }
    }

}
