<?php namespace Maatwebsite\Excel\Classes;

use Closure;
use Config;
use PHPExcel_Worksheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Writers\CellWriter;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

/**
 *
 * Laravel wrapper for PHPExcel_Worksheet
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @copyright  Original Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class LaravelExcelWorksheet extends PHPExcel_Worksheet
{
    /**
     * Parent
     * @var PHPExcel
     */
    public $_parent;

    /**
     * Parser
     * @var ViewParser
     */
    protected $parser;

    /**
     * View
     * @var string
     */
    public $view;

    /**
     * Data
     * @var array
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
        'orientation',
        'paperSize',
        'scale',
        'fitToPage',
        'fitToHeight',
        'fitToWidth',
        'columnsToRepeatAtLeft',
        'rowsToRepeatAtTop',
        'horizontalCentered',
        'verticalCentered',
        'printArea',
        'firstPageNumber'
    );

    /**
     * Allowed page setup
     * @var array
     */
    public $allowedStyles = array(
        'fontFamily',
        'fontSize',
        'fontBold'
    );

    /**
     * Check if the file was autosized
     * @var boolean
     */
    public $hasFixedSizeColumns = false;

    /**
     * Auto generate table heading
     * @var [type]
     */
    protected $autoGenerateHeading = true;

    /**
     * Create a new worksheet
     *
     * @param PHPExcel   $pParent
     * @param string     $pTitle
     */
    public function __construct(PHPExcel $pParent = null, $pTitle = 'Worksheet')
    {
        parent::__construct($pParent, $pTitle);
        $this->setParent($pParent);
    }

    /**
     * Set default page setup
     * @return  void
     */
    public function setDefaultPageSetup()
    {
        // Get the page setup
        $pageSetup = $this->getPageSetup();

        foreach($this->allowedPageSetup as $setup)
        {
            // set the setter
            list($setter, $set) = $this->_setSetter($setup);

            // get the value
            $value = Config::get('excel::sheets.pageSetup.' . $setup, NULL);

            // Set the page setup value
            if(!is_null($value))
                call_user_func_array(array($pageSetup, $setter), array($value));
        }

        // Set default page margins
        $this->setPageMargin(Config::get('excel::export.sheets.page_margin', false));
    }

    /**
     * Set the page margin
     * @param array|boolean|integer|float $margin
     */
    public function setPageMargin($margin = false)
    {
        if(!is_array($margin))
        {
            $marginArray = array($margin, $margin, $margin, $margin);
        }
        else
        {
            $marginArray = $margin;
        }

        // Get margin
        $pageMargin = $this->getPageMargins();

        if(isset($marginArray[0]))
            $pageMargin->setTop($marginArray[0]);

        if(isset($marginArray[1]))
            $pageMargin->setRight($marginArray[1]);

        if(isset($marginArray[2]))
            $pageMargin->setBottom($marginArray[2]);

        if(isset($marginArray[3]))
            $pageMargin->setLeft($marginArray[3]);
    }

    /**
     * Manipulate a single row
     * @param  integer|callback|array  $rowNumber
     * @param  array|callback $callback
     * @return LaravelExcelWorksheet
     */
    public function row($rowNumber, $callback = null)
    {
        // If a callback is given, handle it with the cell writer
        if($callback instanceof Closure)
        {
            $range = $this->rowToRange($rowNumber);
            return $this->cells($range, $callback);
        }

        // Else if the 2nd param was set, we will use it as a cell value
        if(is_array($callback))
        {
            // Interpret the callback as cell values
            $values = $callback;

            // Set start column
            $column = 'A';

            foreach($values as $rowValue)
            {
                // Set cell coordinate
                $cell = $column . $rowNumber;

                // Set the cell value
                $this->setCellValue($cell, $rowValue);
                $column++;
            }

        }

        return $this;
    }

    /**
     * Add multiple rows
     * @param  array  $rows
     * @return LaravelExcelWorksheet
     */
    public function rows($rows = array())
    {
        // Get the start row
        $startRow = $this->getStartRow();

        // Add rows
        foreach($rows as $row)
        {
            $this->row($startRow, $row);
            $startRow++;
        }

        return $this;
    }

    /**
     * Prepend a row
     * @param  integer $rowNumber
     * @param  array|callback  $callback
     * @return LaravelExcelWorksheet
     */
    public function prependRow($rowNumber = 1, $callback = null)
    {
        // If only one param was given, prepend it before the first row
        if(is_null($callback))
        {
            $callback = $rowNumber;
            $rowNumber = 1;
        }

        // Create new row
        $this->insertNewRowBefore($rowNumber);

        // Add data to row
        return $this->row($rowNumber, $callback);
    }

    /**
     * Append a row
     * @param  integer|callback $rowNumber
     * @param  array|callback  $callback
     * @return LaravelExcelWorksheet
     */
    public function appendRow($rowNumber = 1, $callback = null)
    {
        // If only one param was given, add it as very last
        if(is_null($callback))
        {
            $callback = $rowNumber;
            $rowNumber = $this->getStartRow();
        }

        // Add the row
        return $this->row($rowNumber, $callback);
    }

    /**
     * Manipulate a single cell
     * @param  array|string  $cell
     * @param  callback $callback
     * @return LaravelExcelWorksheet
     */
    public function cell($cell, $callback = false)
    {
        // If a callback is given, handle it with the cell writer
        if($callback instanceof Closure)
            return $this->cells($cell, $callback);

        // Else if the 2nd param was set, we will use it as a cell value
        if($callback)
            $this->sheet->setCellValue($cell, $callback);

        return $this;
    }

    /**
     * Manipulate a cell or a range of cells
     * @param  array $cells
     * @param callback $callback
     * @return LaravelExcelWorksheet
     */
    public function cells($cells, $callback = false)
    {
        // Init the cell writer
        $cells = new CellWriter($cells, $this);

        // Do the callback
        if($callback instanceof Closure)
            call_user_func($callback, $cells);

        return $this;
    }

    /**
     *  Load a View and convert to HTML
     *  @param string $view
     *  @param array $data
     *  @param array $mergeData
     *  @return LaravelExcelWorksheet
     */
    public function setView()
    {
        return call_user_func_array(array($this, 'loadView'), func_get_args());
    }

    /**
     *  Load a View and convert to HTML
     *  @param string $view
     *  @param array $data
     *  @param array $mergeData
     *  @return LaravelExcelWorksheet
     */
    public function loadView($view, $data = array(), $mergeData = array())
    {
        // Init the parser
        if(!$this->parser)
            $this->setParser();

        $this->parser->setView($view);
        $this->parser->setData($data);
        $this->parser->setMergeData($mergeData);

        return $this;
    }

    /**
     * Unset the view
     * @return LaravelExcelWorksheet
     */
    public function unsetView()
    {
        $this->parser = null;
        return $this;
    }

    /**
     * Set the parser
     * @param boolean $parser
     * @return ViewParser
     */
    public function setParser($parser = false)
    {
        return $this->parser = $parser ? $parser : app('excel.parsers.view');
    }

    /**
     * Get the view
     * @return ViewParser
     */
    public function getView()
    {
        return $this->parser;
    }

     /**
     * Return parsed sheet
     * @return LaravelExcelWorksheet
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
     * @param  string   $keys
     * @param  string   $value
     * @param  boolean  $headingGeneration
     * @return LaravelExcelWorksheet
     */
    public function with($key, $value = false, $headingGeneration = true)
    {
        // Set the heading generation setting
        $this->setAutoHeadingGeneration($headingGeneration);

        // Add the vars
        $this->_addVars($key, $value);
        return $this;
    }

    /**
     * From array
     * @param  Collection|array $source
     * @param boolean $headingGeneration
     * @return LaravelExcelWorksheet
     */
    public function fromModel($source = NULL, $nullValue = null, $startCell = false, $strictNullComparison = false, $headingGeneration = true)
    {
        return $this->fromArray($source, $nullValue, $startCell, $strictNullComparison, $headingGeneration);
    }

    /**
     * Fill worksheet from values in array
     *
     * @param array $source Source array
     * @param mixed $nullValue Value in source array that stands for blank cell
     * @param string $startCell Insert array starting from this cell address as the top left coordinate
     * @param boolean $strictNullComparison Apply strict comparison when testing for null values in the array
     * @throws PHPExcel_Exception
     * @return LaravelExcelWorksheet
     */
    public function fromArray($source = null, $nullValue = null, $startCell = false, $strictNullComparison = false, $headingGeneration = true)
    {
        // Set defaults
        $nullValue            = !is_null($nullValue) ? $nullValue : $this->getDefaultNullValue();
        $startCell            = $startCell ? $startCell : $this->getDefaultStartCell();
        $strictNullComparison = $strictNullComparison ? $strictNullComparison : $this->getDefaultStrictNullComparison();

        // Set the heading generation setting
        $this->setAutoHeadingGeneration($headingGeneration);

        // Add the vars
        $this->_addVars($source);

        return parent::fromArray($this->data, $nullValue, $startCell, $strictNullComparison);
    }

    /**
     * Add vars to the data array
     * @param string  $key
     * @param string $value
     * @return void
     */
    protected function _addVars($key, $value = false)
    {
        // Add array of data
        if(is_array($key) || $key instanceof Collection)
        {
            // Set the data
            $this->data = $this->addData($key);

            // Create excel from array without a view
            if(!$this->parser)
            {
                $nullValue            = $this->getDefaultNullValue();
                $startCell            = $this->getDefaultStartCell();
                $strictNullComparison = $this->getDefaultStrictNullComparison();
                return parent::fromArray($this->data, $nullValue, $startCell, $strictNullComparison);
            }
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
     * Add data
     * @param array $array
     * @return  array
     */
    protected function addData($array)
    {
        // If a parser was set
        if($this->parser)
        {
            // Don't change anything
            $data = $array;
        }
        else
        {
            // Transform model/collection to array
            if($array instanceof Collection)
                $array = $array->toArray();

            // Get the firstRow
            $firstRow = reset($array);

            // Check if the array has array values
            if(count($firstRow) != count($firstRow, 1))
            {
                // Loop through the data to remove arrays
                $data = array();
                $r = 0;
                foreach($array as $row)
                {
                    $data[$r] = array();
                    foreach($row as $key => $cell)
                    {
                        if(!is_array($cell))
                        {
                            $data[$r][$key] = $cell;
                        }
                    }
                    $r++;
                }
            }
            else
            {
                $data = $array;
            }

            // Check if we should auto add the first row based on the indices
            if($this->generateHeadingByIndices())
            {
                // Get the first row
                $firstRow = reset($data);

                if(is_array($firstRow))
                {
                    // Get the array keys
                    $tableHeading = array_keys($firstRow);

                    // Add table headings as first row
                    array_unshift($data, $tableHeading);
                }
            }

        }

        // Add results
        if(!empty($data))
            $this->data = !empty($this->data) ? array_merge($this->data, $data) : $data;

        // return data
        return $this->data;
    }

    /**
     * Set the auto heading generation setting
     * @param boolean $boolean
     * @return LaravelExcelWorksheet
     */
    public function setAutoHeadingGeneration($boolean)
    {
        $this->autoGenerateHeading = $boolean;
        return $this;
    }

    /**
     * Disable the heading generation
     * @param  boolean $boolean
     * @return LaravelExcelWorksheet
     */
    public function disableHeadingGeneration($boolean = false)
    {
        $this->setAutoHeadingGeneration($boolean);
        return $this;
    }

    /**
     * Check if we should auto generate the table heading
     * @return boolean
     */
    protected function generateHeadingByIndices()
    {
        if(!$this->autoGenerateHeading)
            return false;

        return Config::get('excel::export.generate_heading_by_indices', false);
    }

    /**
     * Set attributes
     * @param string $key
     * @param array|string $params
     * @return  void|PHPExcel_Worksheet_PageSetup
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

        throw new LaravelExcelException('[ERROR] Laravel Worksheet method ['. $setter .'] does not exist.');
    }

    /**
     * Set default styles
     * @param string $setter
     * @param string $key
     * @param array|string $params
     * @return  PHPExcel_Style
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
     * @param array $styles
     * @return  LaravelExcelWorksheet
     */
    public function setStyle($styles)
    {
        $this->getDefaultStyle()->applyFromArray($styles);
        return $this;
    }

    /**
     * Set the font
     * @param  array $fonts
     * @return LaravelExcelWorksheet
     */
    public function setFont($fonts)
    {
        foreach($fonts as $key => $value)
        {
            $this->setFontStyle($this->getDefaultStyle(), $key, $key, $value);
        }
        return $this;
    }

    /**
     * Set default font styles
     * @param string $caller
     * @param string $setter
     * @param string $key
     * @param array|string $params
     * @return  PHPExcel_Style
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
     * @param string $setter
     * @return  array
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
     * @param PHPExcel $parent
     */
    public function setParent($parent)
    {
        $this->_parent = $parent;
    }

    /**
     * Get the parent excel obj
     * @return PHPExcel
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Set the column width
     * @param string|array  $column
     * @param boolean $value
     * @return  LaravelExcelWorksheet
     */
    public function setWidth($column, $value = false)
    {
        // if is array of columns
        if(is_array($column))
        {
            // Set width for each column
            foreach($column as $subColumn => $subValue)
            {
                $this->setWidth($subColumn, $subValue);
            }
        }
        else
        {
            // Disable the autosize and set column width
            $this->getColumnDimension($column)
                 ->setAutoSize(false)
                 ->setWidth($value);

            // Set autosized to true
            $this->hasFixedSizeColumns = true;
        }

        return $this;
    }

    /**
     * Set the row height
     * @param integer|array  $row
     * @param boolean $value
     * @return  LaravelExcelWorksheet
     */
    public function setHeight($row, $value = false)
    {
        // if is array of columns
        if(is_array($row))
        {
            // Set width for each column
            foreach($row as $subRow => $subValue)
            {
                $this->setHeight($subRow, $subValue);
            }
        }
        else
        {
            // Set column width
            $this->getRowDimension($row)->setRowHeight($value);
        }

        return $this;
    }

    /**
     * Set cell size
     * @param array|string  $cell
     * @param integer $width
     * @param integer $height
     * @return  LaravelExcelWorksheet
     */
    public function setSize($cell, $width = false, $height = false)
    {
        // if is array of columns
        if(is_array($cell))
        {
            // Set width for each column
            foreach($cell as $subCell => $sizes)
            {
                $this->setSize($subCell, reset($sizes), end($sizes));
            }
        }
        else
        {
            // Split the cell to column and row
            list($column, $row) = preg_split('/(?<=[a-z])(?=[0-9]+)/i',$cell);

            if($column)
                $this->setWidth($column, $width);

            if($row)
                $this->setHeight($row, $height);

        }

        return $this;
    }

    /**
     * Autosize column for document
     * @param  array|boolean $columns
     * @return void
     */
    public function setAutoSize($columns = false)
    {
        // Remember that the sheet was autosized
        $this->hasFixedSizeColumns = $columns || !empty($columns) ? false : true;

        // Set autosize to true
        $this->autoSize = $columns ? $columns : false;

        // If is not an array
        if(!is_array($columns) && $columns)
        {
            // Get the highest column
            $toCol = $this->getHighestColumn();

            // Lop through the columns and set the auto size
            $toCol++;
            for ($i = 'A'; $i !== $toCol; $i++) {
                $this->getColumnDimension($i)->setAutoSize(true);
            }
        }

        // Set autosize for the given columns
        elseif(is_array($columns))
        {
            foreach($columns as $column)
            {
                $this->getColumnDimension($column)->setAutoSize(true);
            }
        }

        // Calculate the column widths
        $this->calculateColumnWidths();

        return $this;
    }

    /**
     * Get Auto size
     * @return bool
     */
    public function getAutosize()
    {
        if(isset($this->autoSize))
            return $this->autoSize;

        return Config::get('excel::export.autosize', true);
    }

    /**
     * Check if the sheet was auto sized dynamically
     * @return boolean
     */
    public function hasFixedSizeColumns()
    {
        return $this->hasFixedSizeColumns ? true : false;
    }

    /**
     * Set the auto filter
     * @param boolean $value
     * @return  LaravelExcelWorksheet
     */
    public function setAutoFilter($value = false)
    {
        $value = $value ? $value : $this->calculateWorksheetDimension();
        parent::setAutoFilter($value);
        return $this;
    }

    /**
     *  Freeze or lock rows and columns
     *  @param string $pane rows and columns
     *  @return LaravelExcelWorksheet
     */
    public function setFreeze($pane = 'A2')
    {
        $this->freezePane($pane);
        return $this;
    }

    /**
     * Freeze the first row
     * @return  LaravelExcelWorksheet
     */
    public function freezeFirstRow()
    {
        $this->setFreeze('A2');
        return $this;
    }

    /**
     * Freeze the first column
     * @return  LaravelExcelWorksheet
     */
    public function freezeFirstColumn()
    {
        $this->setFreeze('B1');
        return $this;
    }

    /**
     * Freeze the first row and column
     * @return  LaravelExcelWorksheet
     */
    public function freezeFirstRowAndColumn()
    {
        $this->setFreeze('B2');
        return $this;
    }

    /**
     *  Set a range of cell borders
     *  @param string $pane Start and end of the cell (A1:F10)
     *  @param string $weight Border style
     *  @return LaravelExcelWorksheet
     */
    public function setBorder($pane = 'A1', $weight = 'thin')
    {
        // Set all borders
        $this->getStyle($pane)
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle($weight);

        return $this;
    }

    /**
     *  Set all cell borders
     *  @param string $weight Border style (Reference setBorder style list)
     *  @return LaravelExcelWorksheet
     */
    public function setAllBorders($weight = 'thin')
    {
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => $weight
                )
            )
        );

        // Apply the style
        $this->getDefaultStyle()
            ->applyFromArray($styleArray);

        return $this;
    }

    /**
     *  Set the cell format of the column
     *  @param array $formats An array of cells you want to format columns
     *  @return LaravelExcelWorksheet
     */
    public function setColumnFormat(Array $formats){

        // Loop through the columns
        foreach ($formats as $column => $format) {

            // Change the format for a specific cell or range
            $this->getStyle($column)
                ->getNumberFormat()
                ->setFormatCode($format);
        }

        return $this;
    }

    /**
     * Merge cells
     * @param  string $pRange
     * @return LaravelExcelWorksheet
     */
    public function mergeCells($pRange = 'A1:A1', $alignment = false)
    {
        // Merge the cells
        parent::mergeCells($pRange);

        // Set center alignment on merge cells
        $this->cells($pRange, function($cell) use ($alignment) {
            $aligment = is_string($alignment) ? $alignment : Config::get('excel::export.merged_cell_alignment', 'left');
            $cell->setAlignment($aligment);
        });

        return $this;
    }

    /**
     *  Set the columns you want to merge
     *  @return LaravelExcelWorksheet
     *  @param array $mergeColumn An array of columns you want to merge
     */
    public function setMergeColumn(Array $mergeColumn, $alignment = false)
    {
        foreach ($mergeColumn['columns'] as $column) {
            foreach ($mergeColumn['rows'] as $row) {
                $this->mergeCells($column.$row[0].":".$column.$row[1], $alignment);
            }
        }

        return $this;
    }

    /**
     * Return the start row
     * @return integer
     */
    protected function getStartRow()
    {
        if($this->getHighestRow() == 1)
            return 1;

        return $this->getHighestRow() + 1;
    }

    /**
     * Return range from row
     * @param  integer  $rowNumber
     * @return string $range
     */
    protected function rowToRange($rowNumber)
    {
        return 'A' . $rowNumber . ':' . $this->getHighestColumn() . $rowNumber;
    }

    /**
     * Return default null value
     * @return string|integer|null
     */
    protected function getDefaultNullValue()
    {
        return Config::get('excel::export.sheets.nullValue', null);
    }

    /**
     * Return default null value
     * @return string|integer|null
     */
    protected function getDefaultStartCell()
    {
        return Config::get('excel::export.sheets.startCell', 'A1');
    }


    /**
     * Return default strict null comparison
     * @return boolean
     */
    protected function getDefaultStrictNullComparison()
    {
        return Config::get('excel::export.sheets.strictNullComparison', false);
    }

    /**
     * Dynamically call methods
     * @param  string $method
     * @param  array $params
     * @throws LaravelExcelException
     * @return LaravelExcelWorksheet
     */
    public function __call($method, $params)
    {
        // If the dynamic call starts with "with", add the var to the data array
        if(starts_with($method, 'with'))
        {
            $key = lcfirst(str_replace('with', '', $method));
            $this->_addVars($key, reset($params));
            return $this;
        }

        // If it's a setter
        elseif(starts_with($method, 'set') )
        {
            // set the attribute
            $this->_setAttributes($method, $params);
            return $this;
        }

        throw new LaravelExcelException('[ERROR] Laravel Worksheet method ['. $method .'] does not exist.');
    }
}