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
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Collections\SheetCollection;
use Maatwebsite\Excel\Collections\RowCollection;
use Maatwebsite\Excel\Collections\CellCollection;

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

        // Set the columns
        $this->columns = $columns;

        if(!$this->isParsed)
        {
            // Set worksheet count
            $i = 0;

            // Loop through the worksheets
            foreach($this->excel->getWorksheetIterator() as $this->worksheet)
            {
                // Set the active worksheet
                $this->excel->setActiveSheetIndex($i);

                // Get the worksheet name
                $title = $this->excel->getActiveSheet()->getTitle();

                // Fetch the labels
                $this->indices = $this->reader->hasHeading() ? $this->getIndices() : array();

                // Parse the rows
                $worksheet = $this->parseRows();

                // Get the sheet count
                $this->sheetCount = $this->excel->getSheetCount();

                // If multiple sheets
                if($this->sheetCount > 1)
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

                $i++;

            }
        }

        $this->isParsed = true;

        // Return itself
        return $workbook;
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
            if($this->reader->limit && $this->r == ($this->reader->limit + 1) )
                break;

            // Ignore first row when needed
            if($this->r >= $ignore)
                $parsedRows->push($this->parseCells());

            // Count the rows
            $this->r++;
        }

        // Return the parsed array
        return $parsedRows;
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
        $cellIterator->setIterateOnlyExistingCells($this->reader->needsIgnoreEmpty());

        // Foreach cells
        foreach ($cellIterator as $this->cell) {

            // Get the cell index
            $index = PHPExcel_Cell::columnIndexFromString($this->cell->getColumn());

            // Check how we need to save the parsed array
            $index = $this->reader->hasHeading() ? $this->indices[$i] : $i;

            // Check if we want to select this column
            if(empty($this->columns) || (!empty($this->columns) && in_array($index, $this->columns) ) )
            {
                // If the cell is a date time
                if(PHPExcel_Shared_Date::isDateTime($this->cell) || in_array($index, $this->reader->getDateColumns()))
                {
                    if($this->reader->needsDateFormatting())
                    {
                        // Convert excel time to php date object
                        $date = PHPExcel_Shared_Date::ExcelToPHPObject($this->cell->getCalculatedValue())->format(false);

                        // Parse with carbon
                        $date = Carbon::parse($date);

                        // Format the date if wanted
                        $value = $this->reader->getDateFormat() ? $date->format($this->reader->getDateFormat()) : $date;
                    }
                    else
                    {
                        //Format the date to a formatted string
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
                elseif($this->reader->needsCalculation())
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

            }

            $i++;

        }

        // Return array with parsed cells
        return CellCollection::make($parsedCells);

    }

}