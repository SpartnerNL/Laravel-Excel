<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Readers\HTML_reader;

class Excel extends \PHPExcel
{

    public $i = 0;
    public $excel;
    public $object;
    public $title;
    public $ext;
    public $format;
    public $delimiter;
    public $calculate;
    public $limit = false;

    public function __construct()
    {

        parent::__construct();

        // Init the PHP excel class
        $this->excel = new \PHPExcel();

        // Set defaults
        $this->delimiter = \Config::get('excel::delimiter');
        $this->calculate = \Config::get('excel::calculate');
        $this->ignoreEmpty = \Config::get('excel::ignoreEmpty');

    }

    public function create($title)
    {

        // Set file title
        $this->title = $title;

        // Set properties
        $this->excel->getProperties()
                    ->setCreator(\Config::get('excel::creator'))
                    ->setTitle($this->title);

        return $this;

    }

    public function load($file, $firstRowAsLabel = false)
    {

        // Set defaults
        $this->file = $file;
        $this->ext = \File::extension($this->file);
        $this->title = basename($this->file, '.' . $this->ext);
        $this->firstRowAsLabel = $firstRowAsLabel;

        // Identify the format
        $this->format = \PHPExcel_IOFactory::identify($this->file);

        // Init the reader
        $this->reader = \PHPExcel_IOFactory::createReader($this->format)
                                                ->setReadDataOnly(true);

        // Load the file
        $this->excel = $this->reader->load($this->file);

        // Return itself
        return $this;
    }

    /**
     * Load a HTML string
     *
     * @param string $string
     * @return static
     */
    public function loadHTML($string){

        // Include the HTML Reader
        include 'Readers/HTML_reader.php';

        $this->reader = new HTML_reader;
        $this->excel = $this->reader->load($string);

        return $this;

    }

    /**
     * Load a View and convert to HTML
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return static
     */
    public function loadView($view, $data = array(), $mergeData = array()){

        // Make the view
        $html = \View::make($view, $data, $mergeData);

        // Load the html
        $this->loadHTML($html);

        return $this;
    }


    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function calculate()
    {
        $this->calculate = true;
        return $this;
    }

    public function select($keys = array())
    {

        // Parse the file
        $this->parseFile();

        // Check if we have selected keys
        if(!empty($keys))
        {

            // Get the already parsed file
            $rows = $this->parsed;

            // Reset the original parsed file
            $this->parsed = array();

            $i = 0;

            // Loop through the rows
            foreach($rows as $row)
            {

                // Loop throug the cells and keys
                foreach($row as $key => $this->cell)
                {

                    // Check if the key is in the array
                    if(in_array($key, $keys))
                    {
                        $this->parsed[$i][$key] = $this->cell;
                    }
                }
                $i++;
            }

        }

        return $this;

    }

    public function limit($amount, $start = 0)
    {

        $this->limit = array($amount, $start);
        return $this;

    }

    public function toArray()
    {

        // Parse the file
        $this->parseFile();

        return (array) $this->parsed;
    }

    public function dump()
    {

        // Parse the file
        $this->parseFile();

        echo '<pre class="container" style="background: #f5f5f5; border: 1px solid #e3e3e3; padding:15px;">';
            print_r($this->parsed);
        echo '</pre>';

    }

    public function sheet($title, $orientation = 'landscape')
    {

        // Set page orientation
        switch ($orientation) {
            case 'portrait':
                $this->orientation = \PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT;
                break;

            case 'landscape':
                $this->orientation = \PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE;
                break;
        }

        // Create a new sheet
        $this->excel->createSheet();

        // Set to current index based on i
        $this->excel->setActiveSheetIndex($this->i);

        // Change sheet settings
        $this->excel->getActiveSheet()
                        ->setTitle($title)
                    ->getPageSetup()
                        ->setOrientation($this->orientation)
                        ->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4)
                        ->setFitToPage(true)
                        ->setFitToWidth(1)
                        ->setFitToHeight(1);

        // Count number of sheets
        $this->i++;

        // Return itself to chain
        return $this;
    }

    public function with($array)
    {

        // Send the variables to the excel sheet
        $this->excel
                ->getActiveSheet()
                    ->fromArray($array);

        return $this;
    }

    public function convert($ext = 'xls')
    {

        // Parse the file
        $this->parseFile();

        // Reset the excel object
        $this->excel = new \PHPExcel();

        if($this->sheetCount > 1)
        {
            // Loop through the worksheets
            foreach($this->parsed as $worksheet => $content)
            {
                // Set the sheet with content
                $this->sheet($worksheet)->with($content);
            }
        }
        else
        {
            // Set sheet with content
            $this->sheet($this->title)->with($this->parsed);
        }

        // Export the file
        $this->export($ext);

    }

    public function export($ext = 'xls')
    {

        // Set the extension
        $this->ext = $ext;

        // Render the XLS
        $this->render();

        // Export the file
        $this->object->save('php://output');

        exit;
    }

    public function save($ext = 'xls')
    {

        // Set the extension
        $this->ext = $ext;

        // Render the XLS
        $this->render();

        // Save the file to specified location
        $this->object->save($this->title . '.' . $this->ext);

        exit;
    }

    private function render()
    {

        // Set the render format
        $this->format = $this->decodeFormat($this->ext);

        // Set to first sheet
        $this->excel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->title . '.'. $this->ext .'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $this->object = \PHPExcel_IOFactory::createWriter($this->excel, $this->format);
    }

    private function parseFile()
    {

        // Set i
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
            if($this->firstRowAsLabel !== false)
            {
                $this->labels =  $this->getLabels();
            }

            $this->sheetCount = $this->excel->getSheetCount();

            if($this->sheetCount > 1)
            {

                // Parse the rows of the worksheet
                $parsed[$title] = $this->parseRows();

            }
            else
            {
                $parsed = $this->parseRows();
            }

            $i++;

        }


        // Limit the result
        if($this->limit !== false)
        {
            $this->parsed = array_slice($parsed, $this->limit[1], $this->limit[0]);
        }
        else
        {
            $this->parsed = $parsed;
        }

        // Return itself
        return $this;
    }

    private function getLabels()
    {

         // Fetch the first row
        $this->row = $this->worksheet->getRowIterator(1)->current();

        $this->labels = array();

        foreach ($this->row->getCellIterator() as $this->cell) {

            // Check if the cell is not empty
            if (!empty($this->cell)) {

                // Expolode the cell on the delimiter
                $this->cells = explode($this->delimiter, $this->cell->getValue());

                $i = 0;

                // Loop through the cells
                foreach($this->cells as $this->cell)
                {
                    // Set the labels
                    $this->labels[$i] = str_replace(' ', '-',strtolower($this->cell));
                    $i++;
                }

            }
        }

        return $this->labels;
    }

    private function parseRows()
    {

        // Set row index to 0
        $this->r = 0;

        $parsedRow = array();

        if($this->firstRowAsLabel !== false)
        {
            $ignore = 1;
        }
        else
        {
            $ignore = 0;
        }

        // Loop through the rows inside the worksheet
        foreach ($this->worksheet->getRowIterator() as $this->row) {

            // Ignore first row
            if($this->r >= $ignore)
            {
                $parsedRow[$this->r - $ignore] = $this->parseCells();
            }

            $this->r++;

        }

        // Return the parsed array
        return $parsedRow;
    }

    private function parseCells()
    {

        $parsedCells = array();

        // Set the cell iterator
        $this->cellIterator = $this->row->getCellIterator();
        $this->cellIterator->setIterateOnlyExistingCells(false);

        // Foreach cells
        foreach ($this->cellIterator as $this->cell) {

            if($this->format == 'CSV')
            {

                $parsedCells = $this->parseCSVCell();
                break;

            }
            else
            {

                // Get the cell index
                $index = \PHPExcel_Cell::columnIndexFromString($this->cell->getColumn());

                // Check if we want calculated values or not
                if($this->calculate !== false)
                {
                    $parsedCells[$index] = $this->cell->getCalculatedValue();
                }
                else
                {
                    $parsedCells[$index] = $this->cell->getValue();
                }

            }

        }

        return $parsedCells;

    }

    private function parseCSVCell()
    {
        // Explode the cell content by the delimiter
        $this->cells = explode($this->delimiter, $this->cell->getValue());

        $i = 0;

        // Loop through the cells
        foreach($this->cells as $newCell)
        {

            // Check how we need to save the parsed array
            if($this->firstRowAsLabel !== false)
            {
                $index = $this->labels[$i];
            }
            else
            {
                $index = $i;
            }

            // Set parsed array
            $parsedCSV[$index] = $newCell;

            $i++;
        }

        return $parsedCSV;
    }

    private function decodeFormat($ext)
    {

        switch($ext)
        {

            case 'xls':
                return 'Excel5';
                break;

            case 'csv':
                return 'CSV';
                break;

            default:
                return 'Excel5';
                break;

        }

    }

}