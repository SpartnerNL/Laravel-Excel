<?php namespace Maatwebsite\Excel\Parsers;

use Carbon\Carbon;
use \PHPExcel_Cell;
use \PHPExcel_IOFactory;
use \PHPExcel_Shared_Date;
use Illuminate\Support\Str;
use \PHPExcel_Style_NumberFormat;
use \PHPExcel_Worksheet_PageSetup;
use Illuminate\Filesystem\Filesystem;
use Maatwebsite\Excel\Parsers\ExcelParser;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

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
    public function parseFile()
    {
        if(!$this->isParsed)
        {
            // Set worksheet count
            $i = 0;

            // Set empty array
            $parsed = array();

            // Loop through the worksheets
            foreach($this->excel->getWorksheetIterator() as $this->worksheet)
            {
                // Set the active worksheet
                $this->excel->setActiveSheetIndex($i);

                // Get the worksheet name
                $title = $this->excel->getActiveSheet()->getTitle();

                // Convert to labels
                if($this->reader->firstRowAsIndex !== false)
                {
                    // Fetch the labels
                    $this->indices =  $this->getIndices();
                }

                // Get the sheet count
                $this->sheetCount = $this->excel->getSheetCount();

                // If we have more than 1 worksheet, seperate them
                if($this->sheetCount > 1)
                {
                    // Parse the rows into seperate worksheets
                    $parsed[$title] = $this->parseRows();
                }
                else
                {
                    // Parse the rows, but neglect the worksheet title
                    $parsed = $this->parseRows();
                }

                $i++;

            }

            // Limit the result
            if($this->reader->limit !== false)
            {
                $parsed = array_slice($parsed, $this->reader->limit[1], $this->reader->limit[0]);
            }
        }

        $this->isParsed = true;

        // Return itself
        return $parsed;
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
            $this->indices[] = Str::slug($this->cell->getValue(), $this->reader->seperator);
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
        $parsedRow = array();

        // If the first row is the label, ignore the first row
        $ignore = $this->reader->firstRowAsIndex !== false ? 1 : 0;

        // Loop through the rows inside the worksheet
        foreach ($this->worksheet->getRowIterator() as $this->row) {

            // Ignore first row (this can be 0 or 1)
            if($this->r >= $ignore)
            {
                // Set the array, always starting with 0, and fill it with parsed cells
                $parsedRow[$this->r - $ignore] = $this->parseCells();
            }

            // Count the rows
            $this->r++;

        }

        // Return the parsed array
        return $parsedRow;
    }

     /**
     *
     *  Parse the cells
     *
     *  @return $this
     *
     */
    protected function parseCells()
    {
        $i = 0;
        $parsedCells = array();

        // Set the cell iterator
        $cellIterator = $this->row->getCellIterator();

        // Ignore empty cells
        $cellIterator->setIterateOnlyExistingCells($this->reader->ignoreEmpty);

        // Foreach cells
        foreach ($cellIterator as $this->cell) {

            // Get the cell index
            $index = PHPExcel_Cell::columnIndexFromString($this->cell->getColumn());

            // Check how we need to save the parsed array
            if($this->reader->firstRowAsIndex !== false)
            {
                // Set label index
                $index = $this->indices[$i];
            }

            // If the cell is a date time
            if(PHPExcel_Shared_Date::isDateTime($this->cell))
            {
                // Check if we want to parse the dates
                if ($this->reader->formatDates !== false)
                {
                    // Convert excel time to php date object
                    $value = PHPExcel_Shared_Date::ExcelToPHPObject($this->cell->getCalculatedValue());

                    // Format the date
                    $value = $value->format($this->reader->dateFormat);

                    // Use carbon to parse the time
                    if($this->reader->useCarbon)
                    {
                        $value = Carbon::parse($value)->{$this->reader->carbonMethod}();
                    }
                }
                else
                {
                    // Format the date to a formatted string
                    $value = (string) PHPExcel_Style_NumberFormat::toFormattedString(
                        $this->cell->getCalculatedValue(),
                        $this->cell->getWorksheet()->getParent()
                            ->getCellXfByIndex($this->cell->getXfIndex())
                            ->getNumberFormat()
                            ->getFormatCode()
                    );
                }
            }

            // Check if we want calculated values or not
            elseif($this->reader->calculate !== false)
            {
                // Get calculated value
                $value = $this->cell->getCalculatedValue();
            }
            else
            {
                // Get real value
                $value = $this->cell->getValue();
            }

            // Set the value
            $parsedCells[$index] = $value;
            $i++;

        }

        // Return array with parsed cells
        return $parsedCells;

    }

}