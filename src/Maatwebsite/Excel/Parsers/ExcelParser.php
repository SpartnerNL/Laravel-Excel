<?php namespace Maatwebsite\Excel\Parsers;

use \Config;
use Carbon\Carbon;
use \PHPExcel_Cell;
use \PHPExcel_Shared_Date;
use Illuminate\Support\Str;
use \PHPExcel_Style_NumberFormat;
use Maatwebsite\Excel\Collections\RowCollection;
use Maatwebsite\Excel\Collections\CellCollection;
use Maatwebsite\Excel\Collections\SheetCollection;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

/**
 *
 * LaravelExcel Excel Parser
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class ExcelParser {

    /**
     * If file is parsed
     * @var boolean
     */
    public $isParsed = false;

    /**
     * Reader object
     * @var [type]
     */
    protected $reader;

    /**
     * Excel object
     * @var [type]
     */
    protected $excel;

    /**
     * Worksheet object
     * @var [type]
     */
    protected $worksheet;

    /**
     * Row object
     * @var [type]
     */
    protected $row;

    /**
     * Cell object
     * @var [type]
     */
    protected $cell;

    /**
     * Indices
     * @var [type]
     */
    protected $indices;

    /**
     * Columns we want to fetch
     * @var array
     */
    protected $columns = array();

    /**
     * Row counter
     * @var integer
     */
    protected $r = 0;

    /**
     * Construct excel parser
     */
    public function  __construct($reader)
    {
        $this->reader = $reader;
        $this->excel = $reader->excel;
    }

    /**
     *
     *  Parse the file
     *
     *  @return $this
     *
     */
    public function parseFile($columns = array())
    {
        // Init new sheet collection
        $workbook = new SheetCollection();

        // Set the selected columns
        $this->setSelectedColumns($columns);

        // If not parsed yet
        if(!$this->isParsed)
        {
            // Set worksheet count
            $this->w = 0;

            // Loop through the worksheets
            foreach($this->excel->getWorksheetIterator() as $this->worksheet)
            {
                // Parse the worksheet
                $worksheet = $this->parseWorksheet();

                // If multiple sheets
                if($this->parseAsMultiple())
                {
                    // Push every sheet
                    $workbook->push($worksheet);
                }
                else
                {
                    // Ignore the sheet collection
                    $workbook = $worksheet;
                    break;
                }
                $this->w++;
            }
        }

        $this->isParsed = true;

        // Return itself
        return $workbook;
    }

    /**
     * Check if we want to parse it as multiple sheets
     * @return [type] [description]
     */
    protected function parseAsMultiple()
    {
        return $this->excel->getSheetCount() > 1 || Config::get('excel::import.force_sheets_collection', false);
    }

    /**
     * Parse the worksheet
     * @return [type] [description]
     */
    protected function parseWorksheet()
    {
        // Set the active worksheet
        $this->excel->setActiveSheetIndex($this->w);

        // Get the worksheet name
        $title = $this->excel->getActiveSheet()->getTitle();

        // Fetch the labels
        $this->indices = $this->reader->hasHeading() ? $this->getIndices() : array();

        // Parse the rows
        return $this->parseRows();
    }

    /**
     *
     *  Get the labels
     *
     *  @return $this
     *
     */
    protected function getIndices()
    {
        // Fetch the first row
        $this->row = $this->worksheet->getRowIterator(1)->current();

        // Set empty labels array
        $this->indices = array();

        // Loop through the cells
        foreach ($this->row->getCellIterator() as $this->cell) {

            // Set labels
            $this->indices[] = Str::slug($this->cell->getValue(), $this->reader->getSeperator());
        }

        // Return the labels
        return $this->indices;
    }

    /**
     *
     *  Parse the rows
     *
     *  @return $this
     *
     */
    protected function parseRows()
    {
        // Set empty parsedRow array
        $parsedRows = new RowCollection();

        // Set if we have to ignore rows
        $ignore = $this->reader->hasHeading() ? 1 : 0;

        // Loop through the rows inside the worksheet
        foreach ($this->worksheet->getRowIterator() as $this->row) {

            // Limit the results
            if($this->checkForLimit())
                break;

            // Ignore first row when needed
            if($this->r >= $ignore)
                // Push the parsed cells inside the parsed rows
                $parsedRows->push($this->parseCells());

            // Count the rows
            $this->r++;
        }

        // Return the parsed array
        return $parsedRows;
    }

    /**
     * Check for the limit
     * @return [type] [description]
     */
    protected function checkForLimit()
    {
        // If we have a limit, check if we hit this limit
        return $this->reader->limit && $this->r == ($this->reader->limit + 1);
    }

    /**
     * Parse the cells of the given row
     * @return [type] [description]
     */
    protected function parseCells()
    {
        $i = 0;
        $parsedCells = array();

        // Set the cell iterator
        $cellIterator = $this->row->getCellIterator();

        // Ignore empty cells if needed
        $cellIterator->setIterateOnlyExistingCells($this->reader->needsIgnoreEmpty());

        // Foreach cells
        foreach ($cellIterator as $this->cell) {

            // Check how we need to save the parsed array
            $index = ($this->reader->hasHeading() && $this->indices[$i]) ? $this->indices[$i] : $this->getIndexFromColumn();

            // Check if we want to select this column
            if($this->cellNeedsParsing($index) )
            {
                // Set the value
                $parsedCells[$index] = $this->parseCell($index);

            }

            $i++;
        }

        // Return array with parsed cells
        return CellCollection::make($parsedCells);
    }

    /**
     * Parse a single cell
     * @return [type] [description]
     */
    protected function parseCell($index)
    {
        // If the cell is a date time
        if($this->cellIsDate($index))
        {
            // Parse the date
            return $this->parseDate();
        }

        // Check if we want calculated values or not
        elseif($this->reader->needsCalculation())
        {
            // Get calculated value
            return $this->getCalculatedValue();
        }
        else
        {
            // Get real value
            return $this->getCellValue();
        }
    }

    /**
     * Return the cell value
     * @return [type] [description]
     */
    protected function getCellValue()
    {
        $value = $this->cell->getValue();
        return $this->encode($value);
    }

    /**
     * Get the calculated value
     * @return [type] [description]
     */
    protected function getCalculatedValue()
    {
        $value = $this->cell->getCalculatedValue();
        return $this->encode($value);
    }

    /**
     * Encode with iconv
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    protected function encode($value)
    {
        // Get input and output encoding
        list($input, $output) = array_values(Config::get('excel::import.encoding', array('UTF-8', 'UTF-8')));

        // If they are the same, return the value
        if($input == $output)
            return $value;

        // Encode
        return iconv($input, $output, $value);
    }

    /**
     * Parse the date
     * @return [type] [description]
     */
    protected function parseDate()
    {
        // If the date needs formatting
        if($this->reader->needsDateFormatting())
        {
            // Parse the date with carbon
            return $this->parseDateAsCarbon();
        }
        else
        {
            // Parse the date as a normal string
            return $this->parseDateAsString();
        }
    }

    /**
     * Parse and return carbon object or formatted time string
     * @return [type] [description]
     */
    protected function parseDateAsCarbon()
    {
        // Convert excel time to php date object
        $date = PHPExcel_Shared_Date::ExcelToPHPObject($this->cell->getCalculatedValue())->format(false);

        // Parse with carbon
        $date = Carbon::parse($date);

        // Format the date if wanted
        return $this->reader->getDateFormat() ? $date->format($this->reader->getDateFormat()) : $date;
    }

    /**
     * Return date string
     * @return [type] [description]
     */
    protected function parseDateAsString()
    {
        //Format the date to a formatted string
        return (string) PHPExcel_Style_NumberFormat::toFormattedString(
            $this->cell->getCalculatedValue(),
            $this->cell->getWorksheet()->getParent()
                ->getCellXfByIndex($this->cell->getXfIndex())
                ->getNumberFormat()
                ->getFormatCode()
        );
    }

    /**
     * Check if cell is a date
     * @param  [type] $index [description]
     * @return [type]        [description]
     */
    protected function cellIsDate($index)
    {
        // if is a date or if is a date column
        return PHPExcel_Shared_Date::isDateTime($this->cell) || in_array($index, $this->reader->getDateColumns());
    }

    /**
     * Check if cells needs parsing
     * @return [type] [description]
     */
    protected function cellNeedsParsing($index)
    {
        // if no columns are selected or if the column is selected
        return !$this->hasSelectedColumns() || ($this->hasSelectedColumns() && in_array($index, $this->getSelectedColumns()));
    }

    /**
     * Get the cell index from column
     * @return [type] [description]
     */
    protected function getIndexFromColumn()
    {
        return PHPExcel_Cell::columnIndexFromString($this->cell->getColumn());
    }

    /**
     * Set selected columns
     * @param array $columns [description]
     */
    protected function setSelectedColumns($columns = array())
    {
        // Set the columns
        $this->columns = $columns;
    }

    /**
     * Check if we have selected columns
     * @return boolean [description]
     */
    protected function hasSelectedColumns()
    {
        return !empty($this->columns);
    }

    /**
     * Set selected columns
     * @param array $columns [description]
     */
    protected function getSelectedColumns()
    {
        // Set the columns
        return $this->columns;

    }

}
