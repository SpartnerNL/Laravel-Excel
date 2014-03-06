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

class HTML_reader extends \PHPExcel_Reader_HTML
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

    private $_formats = array(
        'h1' => array(
            'font' => array(
                'bold' => true,
                'size' => 24,
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
     * Loads PHPExcel from file
     *
     * @param   string      $pFilename
     * @return  PHPExcel
     * @throws  PHPExcel_Reader_Exception
     */
    public function load($pFilename, $isString = false)
    {
        // Create new PHPExcel
        $objPHPExcel = new \PHPExcel();

        // Load into this instance
        return $this->loadIntoExisting($pFilename, $objPHPExcel, $isString);
    }

    /**
     * Loads PHPExcel from file into PHPExcel instance
     *
     * @param   string      $pFilename
     * @param   PHPExcel    $objPHPExcel
     * @return  PHPExcel
     * @throws  PHPExcel_Reader_Exception
     */
    public function loadIntoExisting($pFilename, PHPExcel $objPHPExcel, $isString = false)
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

        // Create new PHPExcel
        while ($objPHPExcel->getSheetCount() <= $this->_sheetIndex) {
            $objPHPExcel->createSheet();
        }
        $objPHPExcel->setActiveSheetIndex( $this->_sheetIndex );

        //  Create a new DOM object
        $dom = new \domDocument;
        //  Reload the HTML file into the DOM object

        // Check if we need to load the file or the HTML
        if($isHtmlFile)
        {
            // Load HTML from file
            $loaded = $dom->loadHTMLFile($pFilename);
        }
        else
        {
            // Load HTML from string
            $loaded = @$dom->loadHTML(mb_convert_encoding($pFilename, 'HTML-ENTITIES', 'UTF-8'));
        }

        if ($loaded === FALSE) {
            throw new \PHPExcel_Reader_Exception('Failed to load ',$pFilename,' as a DOM Document');
        }

        //  Discard white space
        $dom->preserveWhiteSpace = true;


        $row = 0;
        $column = 'A';
        $content = '';
        $this->_processDomElement($dom,$objPHPExcel->getActiveSheet(),$row,$column,$content);
        $this->autosizeColumn($objPHPExcel);

        return $objPHPExcel;
    }

    /**
     * Autosize column for document
     *
     * @return int
     */
    public static function autosizeColumn(\PHPExcel $objPHPExcel)
    {
        foreach ($objPHPExcel->getAllSheets() as $sheet) {
            $toCol = $sheet->getHighestColumn();

            $toCol++;
            for ($i = 'A'; $i !== $toCol; $i++) {
                $sheet->getColumnDimension($i)->setAutoSize(true);
            }

            $sheet->calculateColumnWidths();
        }
    }

    private function _processDomElement(\DOMNode $element, $sheet, &$row, &$column, &$cellContent){
        foreach($element->childNodes as $child){
            if ($child instanceof \DOMText) {
                $domText = preg_replace('/\s+/',' ',trim($child->nodeValue));
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
                        ++$row;
                        break;
                    case 'th' :
                    case 'td' :
//                      echo 'START OF TABLE ' , $this->_tableLevel , ' CELL<br />';
                        $this->_processDomElement($child,$sheet,$row,$column,$cellContent);
//                      echo 'END OF TABLE ' , $this->_tableLevel , ' CELL<br />';
                        $this->_flushCell($sheet,$column,$row,$cellContent);
                        ++$column;
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
        foreach($styles as $tag)
        {
            $style = explode(':', $tag);
            $name = trim(reset($style));
            $value = trim(end($style));

            $cells = $sheet->getStyle($column.$row);

            switch($name)
            {

                // BACKGROUND
                case 'background':
                    $value = str_replace('#', '', $value);

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
                    $value = str_replace('#', '', $value);
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

                    $cells->getAlignment()->applyFromArray(
                        array('horizontal' => $horizontal)
                    );

                    break;

                case 'vertical-align':
                    switch($value)
                    {
                        case 'top':
                            $horizontal = PHPExcel_Style_Alignment::VERTICAL_TOP;
                            break;

                        case 'middle':
                            $horizontal = PHPExcel_Style_Alignment::VERTICAL_CENTER;
                            break;

                        case 'bottom':
                            $horizontal = PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
                            break;

                        case 'justify':
                            $horizontal = PHPExcel_Style_Alignment::VERTICAL_JUSTIFY;
                            break;

                    }

                    $cells->getAlignment()->applyFromArray(
                        array('vertical' => $horizontal)
                    );
                    break;

                case 'borders':
                    $borders = explode(' ', $value);
                    $style = $borders[1];
                    $color = end($borders);
                    $color = str_replace('#', '', $color);
                    $borderStyle = $this->borderStyle($style);

                    $cells->getBorders()->applyFromArray(
                        array( 'allborders' => array( 'style' => $borderStyle, 'color' => array( 'rgb' => $color ) ) )
                    );
                    break;

                case 'border-top':
                    $borders = explode(' ', $value);
                    $style = $borders[1];
                    $color = end($borders);
                    $color = str_replace('#', '', $color);

                    $borderStyle = $this->borderStyle($style);

                    $cells->getBorders()->getTop()->applyFromArray(
                        array( 'style' => $borderStyle, 'color' => array( 'rgb' => $color ))
                    );
                    break;

                case 'border-bottom':
                    $borders = explode(' ', $value);
                    $style = $borders[1];
                    $color = end($borders);
                    $color = str_replace('#', '', $color);
                    $borderStyle = $this->borderStyle($style);

                    $cells->getBorders()->getBottom()->applyFromArray(
                        array( 'style' => $borderStyle, 'color' => array( 'rgb' => $color ))
                    );
                    break;

                case 'border-right':
                    $borders = explode(' ', $value);
                    $style = $borders[1];
                    $color = end($borders);
                    $color = str_replace('#', '', $color);
                    $borderStyle = $this->borderStyle($style);

                    $cells->getBorders()->getRight()->applyFromArray(
                        array( 'style' => $borderStyle, 'color' => array( 'rgb' => $color ))
                    );
                    break;

                case 'border-left':
                    $borders = explode(' ', $value);
                    $style = $borders[1];
                    $color = end($borders);
                    $color = str_replace('#', '', $color);
                    $borderStyle = $this->borderStyle($style);

                    $cells->getBorders()->getLeft()->applyFromArray(
                        array( 'style' => $borderStyle, 'color' => array( 'rgb' => $color ))
                    );
                    break;

            }


        }
    }

    public function borderStyle($style)
    {

        switch($style) {
            case 'solid';
                return PHPExcel_Style_Border::BORDER_DASHDOT;
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
