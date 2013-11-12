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
    public $delimiter = ';';

    public function __construct()
    {

        parent::__construct();

        // Init the PHP excel class
        $this->excel = new \PHPExcel();

    }

    public function create($title)
    {

        // Set file title
        $this->title = $title;

        // Set properties
        $this->excel->getProperties()
                    ->setCreator(\Config::get('Maatwebsite/excel::creator'))
                    ->setTitle($this->title);

        return $this;

    }

    public function load($file, $firstRowAsIndex = true)
    {
        $this->file = $file;
        $this->ext = \File::extension($this->file);
        $this->format = $this->decodeFormat($this->ext);

        // Create a reader
        $this->reader = \PHPExcel_IOFactory::createReader($this->format);

        // Load the file
        $this->excel = $this->reader->load($this->file);

        // Parse the file
        $this->parseFile();

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

    public function select($keys = array())
    {

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
                foreach($row as $key => $cell)
                {

                    // Check if the key is in the array
                    if(in_array($key, $keys))
                    {
                        $this->parsed[$i][$key] = $cell;
                    }
                }
                $i++;
            }

        }

        return $this;

    }

    public function toArray()
    {
        return (array) $this->parsed;
    }

    public function dump()
    {

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

        // Set the extension
        $this->ext = $ext;

        // Render the XLS
        $this->render();

        // Export the file
        $this->object->save('php://output');

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

        // Get the current worksheet
        $this->worksheet = $this->excel->getActiveSheet();

        // Set empty array
        $this->parsed = array();

        // Convert to labels
        $this->labels =  $this->getLabels();

        $this->parsed = $this->parseRows();

        return $this;
    }

    private function getLabels()
    {

         // Fetch the first row
        $this->row = $this->worksheet->getRowIterator(1)->current();

        $cellIterator = $this->row->getCellIterator();

        foreach ($cellIterator as $cell) {
            if (!is_null($cell)) {

                $cells = explode($this->delimiter, $cell->getValue());

                $i = 1;
                foreach($cells as $cell)
                {
                    $this->labels[$i] = strtolower($cell);
                    $i++;
                }

            }
        }

        return $this->labels;
    }

    private function parseRows()
    {

        // Set row index to 0
        $r = 0;

        // Loop through the rows inside the worksheet
        foreach ($this->worksheet->getRowIterator() as $this->row) {

            // Ignore first row
            if($r >= 1)
            {

                // Set the cell iterator
                $cellIterator = $this->row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                // Foreach cells
                foreach ($cellIterator as $cell) {

                    // Check if not empty
                    if (!empty($cell)) {

                        // Explode the cell content by the delimiter
                        $cells = explode($this->delimiter, $cell->getValue());

                        $i = 1;

                        // Loop through the cells
                        foreach($cells as $newCell)
                        {
                            // Set parsed array
                            $this->parsed[$r][$this->labels[$i]] = $newCell;

                            $i++;
                        }

                        // Break after the first cell
                        break;

                    }

                }

            }

            $r++;

        }

        // Return the parsed array
        return $this->parsed;
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