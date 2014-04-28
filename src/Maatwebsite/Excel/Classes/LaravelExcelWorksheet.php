<?php namespace Maatwebsite\Excel\Classes;

use \PHPExcel_Worksheet;
use Maatwebsite\Excel\Parsers\ViewParser;

/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2014 PHPExcel
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
 * @package    PHPExcel_Worksheet
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    ##VERSION##, ##DATE##
 */


/**
 * PHPExcel_Worksheet
 *
 * @category   PHPExcel
 * @package    PHPExcel_Worksheet
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class LaravelExcelWorksheet extends PHPExcel_Worksheet
{

    /**
     * Parent
     * @var [type]
     */
    public $_parent;

    /**
     * Parser
     * @var [type]
     */
    protected $parser;

    /**
     * View
     * @var [type]
     */
    public $view;

    /**
     * Data
     * @var [type]
     */
    public $data = array();

    /**
     * Merge data
     * @var array
     */
    public $mergeData = array();

    /**
     * Allowed page setup
     * @var array
     */
    public $allowedPageSetup = array(
        'orientation', 'paperSize', 'scale', 'fitToPage', 'fitToHeight', 'fitToWidth', 'columnsToRepeatAtLeft', 'rowsToRepeatAtTop', 'horizontalCentered', 'verticalCentered', 'printArea', 'firstPageNumber'
    );

    /**
     * Allowed page setup
     * @var array
     */
    public $allowedStyles = array(
        'fontFamily', 'fontSize', 'fontBold'
    );

    /**
     * Create a new worksheet
     *
     * @param PHPExcel        $pParent
     * @param string        $pTitle
     */
    public function __construct(PHPExcel $pParent = null, $pTitle = 'Worksheet')
    {
        parent::__construct($pParent, $pTitle);
        $this->setParent($pParent);
    }

    /**
     * Set default page setup
     */
    public function setDefaultPageSetup()
    {
        $pageSetup = $this->getPageSetup();

        foreach($this->allowedPageSetup as $setup)
        {
            // set the setter
            list($setter, $set) = $this->_setSetter($setup);

            // get the value
            $value = \Config::get('excel::sheets.pageSetup.' . $setup, NULL);

            // Set the page setup value
            if(!is_null($value))
                $pageSetup->{$setter}($value);
        }
    }

    /**
     *
     * Load a View and convert to HTML
     *
     *  @param string $view
     *  @param array $data
     *  @param array $mergeData
     *  @return static
     *
     */
    public function loadView($view, $data = array(), $mergeData = array())
    {
        // Init the parser
        if(!$this->parser)
            $this->parser = app('excel.parsers.view');

        $this->parser->setView($view);
        $this->parser->setData($data);
        $this->parser->setMergeData($mergeData);

        return $this;
    }

    /**
     * Get the view
     * @return [type] [description]
     */
    public function getView()
    {
        return $this->parser;
    }

     /**
     * Return parsed sheet
     * @return [type] [description]
     */
    public function parsed()
    {
        // If parser is set, use it
        if($this->parser)
            return $this->parser->parse($this);

        // Else return the entire sheet
        return $this;
    }

    /**
     * Set data for the current sheet
     * @param  [type]  $keys  [description]
     * @param  boolean $value [description]
     * @return [type]         [description]
     */
    public function with($key, $value = false)
    {
        // Add the vars
        $this->_addVars($key, $value);
    }

    /**
     * Add vars to the data array
     * @param [type]  $key   [description]
     * @param boolean $value [description]
     */
    protected function _addVars($key, $value = false)
    {
        // Add array of data
        if(is_array($key))
        {
            // Set the data
            $this->data = array_merge($this->data, $key);

            // Create excel from array without a view
            if(!$this->parser)
                $this->fromArray($this->data);
        }

        // Add seperate values
        else
        {
            $this->data[$key] = $value;
        }

        // Set data to parser
        if($this->parser)
            $this->parser->setData($this->data);
    }

    /**
     * Set attributes
     * @param [type] $key    [description]
     * @param [type] $params [description]
     */
    public function _setAttributes($setter, $params)
    {
        // Set the setter and the key
        list($setter, $key) = $this->_setSetter($setter);

        // If is page setup
        if(in_array($key, $this->allowedPageSetup))
        {
            // Set params
            $params = is_array($params) ? $params : array($params);

            // Call the setter
            return call_user_func_array(array($this->getPageSetup(), $setter), $params);
        }

        // If is a style
        elseif(in_array($key, $this->allowedStyles) )
        {
            $this->setDefaultStyles($setter, $key, $params);
        }
    }

    /**
     * Set default styles
     * @param [type] $setter [description]
     * @param [type] $key    [description]
     * @param [type] $params [description]
     */
    protected function setDefaultStyles($setter, $key, $params)
    {
        $caller = $this->getDefaultStyle();
        $params = is_array($params) ? $params : array($params);

        if(str_contains($key, 'font'))
            return $this->setFontStyle($caller, $setter, $key, $params);

        return call_user_func_array(array($caller, $setter), $params);
    }

    /**
     * Set default styles by array
     * @param [type] $styles [description]
     */
    public function setStyle($styles)
    {
        $this->getDefaultStyle()->applyFromArray($styles);
    }

    /**
     * Set the font
     * @param  [type] $fonts [description]
     * @return [type]        [description]
     */
    public function setFont($fonts)
    {
        foreach($fonts as $key => $value)
        {
            $this->setFontStyle($this->getDefaultStyle(), $key, $key, $value);
        }
    }

    /**
     * Set default font styles
     * @param [type] $caller [description]
     * @param [type] $setter [description]
     * @param [type] $key    [description]
     * @param [type] $params [description]
     */
    protected function setFontStyle($caller, $setter, $key, $params)
    {
        // Set caller to font
        $caller = $caller->getFont();
        $params = is_array($params) ? $params : array($params);

        // Clean the setter name
        $key = lcfirst(str_replace('font', '', $key));

        // Get setter method
        list($setter, $key) = $this->_setSetter($key);

        switch($key)
        {
            case 'family':
                $setter = 'setName';
                break;
        }

        return call_user_func_array(array($caller, $setter), $params);
    }

    /**
     * Set the setter
     * @param [type] $setter [description]
     */
    protected function _setSetter($setter)
    {
        if(starts_with($setter, 'set'))
        {
            $key = lcfirst(str_replace('set', '', $setter));
        }
        else
        {
            $key = $setter;
            $setter = 'set' . ucfirst($key);
        }

        // Return the setter method and the key
        return array($setter, $key);
    }

     /**
     * Set the parent (excel object)
     * @param [type] $parent [description]
     */
    public function setParent($parent)
    {
        $this->_parent = $parent;
    }

    /**
     * Get the parent excel obj
     * @return [type] [description]
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Autosize column for document
     *
     * @return int
     */
    public function setAutoSize($columns = false)
    {
        if(!is_array($columns) && $columns)
        {
            $toCol = $this->getHighestColumn();

            $toCol++;
            for ($i = 'A'; $i !== $toCol; $i++) {
                $this->getColumnDimension($i)->setAutoSize(true);
            }
        }
        elseif(is_array($columns))
        {
            foreach($columns as $column)
            {
                $this->getColumnDimension($column)->setAutoSize(true);
            }
        }

        $this->calculateColumnWidths();

    }

    /**
     * Set the auto filter
     * @param boolean $value [description]
     */
    public function setAutoFilter($value = false)
    {
        $value = $value ? $value : $this->calculateWorksheetDimension();
        parent::setAutoFilter($value);
        return $this;
    }

    /**
     *
     *  Freeze or lock rows and columns
     *
     *  @param string $pane rows and columns , default freeze the first row
     *  @return $this
     *
     *  @author xiehai
     *  @example ->setFreeze()          Freeze the first row
     *           ->setFreeze('B1')      Freeze the first column (THE A COLUMN)
     *           ->setFreeze('B2')      Freeze the first row and first column
     *
     */
    public function setFreeze($pane = 'A2')
    {
        $this->freezePane($pane);
        return $this;
    }

    /**
     * Freeze the first row
     * @return  $this
     */
    public function freezeFirstRow()
    {
        $this->setFreeze('A2');
        return $this;
    }

    /**
     * Freeze the first column
     * @return  $this
     */
    public function freezeFirstColumn()
    {
        $this->setFreeze('B1');
        return $this;
    }

    /**
     * Freeze the first row and column
     * @return  $this
     */
    public function freezeFirstRowAndColumn()
    {
        $this->setFreeze('B2');
        return $this;
    }

    /**
     *
     *  Set a range of cell borders
     *
     *  @param string $pane Start and end of the cell (A1:F10)
     *  @param string $weight Border style (Reference setBorder style list)
     *  @return $this
     *  @example ->setBorder('A1:F10','thick')
     *
     */
    public function setBorder($pane = 'A1', $weight = 'thin')
    {
        /*
        @ ~ Border styles list ~ @

        PHPExcel_Style_Border::BORDER_NONE = 'none'
        PHPExcel_Style_Border::BORDER_DASHDOT = 'dashDot'
        PHPExcel_Style_Border::BORDER_DASHDOTDOT = 'dashDotDot'
        PHPExcel_Style_Border::BORDER_DASHED = 'dashed'
        PHPExcel_Style_Border::BORDER_DOTTED = 'dotted'
        PHPExcel_Style_Border::BORDER_DOUBLE = 'double'
        PHPExcel_Style_Border::BORDER_HAIR = 'hair'
        PHPExcel_Style_Border::BORDER_MEDIUM = 'medium'
        PHPExcel_Style_Border::BORDER_MEDIUMDASHDOT = 'mediumDashDot'
        PHPExcel_Style_Border::BORDER_MEDIUMDASHDOTDOT = 'mediumDashDotDot'
        PHPExcel_Style_Border::BORDER_MEDIUMDASHED = 'mediumDashed'
        PHPExcel_Style_Border::BORDER_SLANTDASHDOT = 'slantDashDot'
        PHPExcel_Style_Border::BORDER_THICK = 'thick'
        PHPExcel_Style_Border::BORDER_THIN = 'thin'
        */

        $weight = $pane == 'A1' ? 'none' : $weight;

        // Set all borders
        $this->excel->getStyle($pane)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle($weight);

        return $this;
    }

    /**
     *
     *  Set all cell borders
     *
     *  @param string $weight Border style (Reference setBorder style list)
     *  @return $this
     *  @example Excel::create()->setAllBorder()   Must follow the function of create()
     *
     */
    public function setAllBorder($weight = 'thin')
    {
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => $weight
                )
            )
        );

        // Apply the style
        $this->excel->getDefaultStyle()
                    ->applyFromArray($styleArray);

        return $this;
    }

    /**
     *
     *  Set the cell format of the column
     *
     *  @return $this
     *  @param array $formats An array of cells you want to format columns
     *
     *  @author xiehai
     *  @example ->setColumnFormat(array(
     *          'B' => '0',
     *          'D' => '0.00',
     *          'F' => '@',
     *          'F' => 'yyyy-mm-dd',
     *          ......
     *      )
     *  )
     *  @uses This method can only be used before the with() method
     *
     */

     /*
      * @ ~ The Format list ~ @
      *
        PHPExcel_Style_NumberFormat::FORMAT_GENERAL = 'General'
        PHPExcel_Style_NumberFormat::FORMAT_TEXT = '@'
        PHPExcel_Style_NumberFormat::FORMAT_NUMBER = '0'
        PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00 = '0.00'
        PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1 = '#,##0.00'
        PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2 = '#,##0.00_-'
        PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE = '0%'
        PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00 = '0.00%'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2 = 'yyyy-mm-dd'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD = 'yy-mm-dd'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY = 'dd/mm/yy'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_DMYSLASH = 'd/m/y'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_DMYMINUS = 'd-m-y'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_DMMINUS = 'd-m'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_MYMINUS = 'm-y'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14 = 'mm-dd-yy'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX15 = 'd-mmm-yy'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX16 = 'd-mmm'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX17 = 'mmm-yy'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX22 = 'm/d/yy h:mm'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME = 'd/m/y h:mm'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME1 = 'h:mm AM/PM'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME2 = 'h:mm:ss AM/PM'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME3 = 'h:mm'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME4 = 'h:mm:ss'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME5 = 'mm:ss'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME6 = 'h:mm:ss'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME7 = 'i:s.S'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME8 = 'h:mm:ss;@'
        PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH = 'yy/mm/dd;@'
        PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE = '"$"#,##0.00_-'
        PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD = '$#,##0_-'
        PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE = '[$EUR ]#,##0.00_-'
      */
    public function setColumnFormat(Array $formats){

        // Loop through the columns
        foreach ($formats as $column => $format) {

            // Change the format for a specific cell or range
            $this->excel->getStyle($column)
                        ->getNumberFormat()
                        ->setFormatCode($format);
        }

        return $this;
    }

    /**
     *
     *  Set the cell width of the columns
     *
     *  @return $this
     *  @param array $pane An array of column widths
     *
     *  @author xiehai
     *  @example ->setColumnWidth(array(
     *          'A' => '10',
     *          'B' => '22',
     *          'F' => '8',
     *          'N' => '13',
     *          ......
     *      )
     *  )
     *
     */
    public function setColumnWidth(Array $pane)
    {
        foreach ($pane as $column => $width) {
            $this->excel->getColumnDimension($column)->setWidth($width);
        }

        return $this;
    }

    /**
     *
     *  Set the columns you want to merge
     *
     *  @return $this
     *  @param array $mergeColumn An array of columns you want to merge
     *
     *  @author xiehai
     *  @example    $mergeColumn = array(
     *                  'columns' => array('A','B','C','D'),
     *                  'rows' => array(
     *                      array(2,3),
     *                      array(5,11),
     *                      .....
     *                   )
     *            );
     *
     */
    public function setMergeColumn(Array $mergeColumn)
    {
        foreach ($mergeColumn['columns'] as $column) {
            foreach ($mergeColumn['rows'] as $row) {
                $this->mergeCells($column.$row[0].":".$column.$row[1]);
            }
        }

        return $this;
    }

    /**
     * Get the sheet index
     * @return [type] [description]
     */
    public function getSheetIndex()
    {
        return $this->_parent->getActiveSheetIndex();
    }

    /**
     * Get style for cell
     *
     * @param string $pCellCoordinate Cell coordinate to get style for
     * @return PHPExcel_Style
     * @throws PHPExcel_Exception
     */
    public function getStyle($pCellCoordinate = 'A1')
    {
        // set cell coordinate as active
        $this->setSelectedCells($pCellCoordinate);

        return $this->_parent->getCellXfSupervisor();
    }

    /**
     * Dynamically call methods
     * @param  [type] $method [description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function __call($method, $params)
    {

        // If the dynamic call starts with "with", add the var to the data array
        if(starts_with($method, 'with'))
        {
            $key = lcfirst(str_replace('with', '', $method));
            $this->_addVars($key, reset($params));
        }

        // If it's a stter
        elseif(starts_with($method, 'set') )
        {
            // set the attribute
            $this->_setAttributes($method, $params);
        }

        return $this;
    }

    /**
     * Reset data on class destruct
     */
    public function __destruct()
    {
        $this->data = array();
        $this->_parent->_cellXfCollection = array();
    }

}
