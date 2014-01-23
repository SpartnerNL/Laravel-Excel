<?php namespace Maatwebsite\Excel;

use Config;
use Maatwebsite\Excel\Readers\HTML_reader;
use \PHPExcel_Shared_Date;

/**
 * Laravel wrapper for PHPEXcel
 *
 * @version 0.1.0
 * @package maatwebsite/excel
 * @author Maatwebsite <info@maatwebsite.nl>
 */

class Excel extends \PHPExcel
{


    protected $excel;
    protected $object;
    public $i = 0; // the current sheet number
    public $title;
    public $ext;
    public $format;
    public $delimiter;
    public $calculate;
    public $limit = false;
    protected $ignoreEmpty = false;
    protected $isParsed = false;
    protected $firstRowAsLabel = false;
    protected $formatDates = true;
    protected $dateFormat = 'Y-m-d';
    protected $useCarbon = false;
    protected $carbonMethod = 'toDateTimeString';

    /**
     *
     *  Constructor
     *
     *  Init the parent, init PHPExcel and set the defaults
     *
     */

    public function __construct()
    {

        parent::__construct();

        // Init the PHP excel class
        try {
            $this->excel = new \PHPExcel();
        } catch(Exception $e) {
            App::abort('500', "Error initing PHPExcel: ".$e->getMessage());
        }


        // Set defaults
        $this->delimiter = Config::get('excel::delimiter');
        $this->calculate = Config::get('excel::calculate');
        $this->ignoreEmpty = Config::get('excel::ignoreEmpty');

    }

    /**
     *
     *  Create a new excel file, with default values and a file title.
     *
     *  @param str $title The file title
     *  @return $this
     *
     */

    public function create($title)
    {

        // Set file title
        $this->title = $title;

        // Remove the default worksheet
        $this->excel->removeSheetByIndex(0);

        // Set properties
        $this->excel->getProperties()
                    ->setCreator(Config::get('excel::creator'))
                    ->setLastModifiedBy(Config::get('excel::creator'))
                    ->setTitle($this->title);

        return $this;

    }

    /**
     *
     *  Load an existing file
     *
     *  @param str $file The file we want to load
     *  @param bool $firstRowAsLabel Do we want to interpret de first row as labels?
     *  @return $this
     *
     */

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
        $this->reader = \PHPExcel_IOFactory::createReader($this->format);

        // Load the file
        $this->excel = $this->reader->load($this->file);

        // Return itself
        return $this;
    }

    /**
     * Set the date format
     * @param str $format The date format
     */
    public function setDateFormat($format)
    {
        $this->dateFormat = $format;
        return $this;
    }

    /**
     * Enable/disable date formating
     * @param  bool $boolean True/false
     */
    public function formatDates($boolean)
    {
        $this->formatDates = $boolean;
        return $this;
    }

    public function useCarbon($method = false)
    {
        $this->useCarbon = true;

        if($method)
        {
            $this->carbonMethod = $method;
        }

        return $this;
    }

    /**
     *
     *  Load a HTML string
     *
     *  @param string $string
     *  @return static
     *
     */

    public function loadHTML($string){

        // Include the HTML Reader
        include 'Readers/HTML_reader.php';

        $this->reader = new HTML_reader;
        $this->excel = $this->reader->load($string);

        return $this;

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

    public function loadView($view, $data = array(), $mergeData = array()){

        // Make the view
        $html = \View::make($view, $data, $mergeData);

        // Load the html
        $this->loadHTML($html);

        return $this;
    }

    /**
     *
     *  Set the delimiter for CSV
     *
     *  @param str $delimiter The delimiter we want to use
     *  @return $this
     *
     */

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     *
     *  Set the file title
     *
     *  @param str $title The file title
     *  @return $this
     *
     */

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     *
     *  Set default calculate
     *
     *  @param bool $do Calculate yes or no
     *  @return $this
     *
     */

    public function calculate($do = true)
    {
        $this->calculate = $do;
        return $this;
    }

    /**
     *
     *  Set the limit
     *
     *  @param int $amount The amount we want to return
     *  @param int $start The position we wil start on
     *  @return $this
     *
     */

    public function limit($amount, $start = 0)
    {

        // Set the limit
        $this->limit = array($amount, $start);
        return $this;

    }

    /**
     *
     *  Select columns from the array
     *
     *  @param array $keys The columns we want to select
     *  @return $this
     *
     */

    public function select($keys = array())
    {

        // Parse the file
        $this->parseFile();

        // Check if we have selected keys
        if(!empty($keys))
        {

            // Get the already parsed file
            $rows = $this->parsed;

            // Reset the original parsed file to an empty array
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
                        // Add to the new parsed array
                        $this->parsed[$i][$key] = $cell;
                    }
                }
                $i++;
            }

        }

        return $this;

    }

    /**
     *
     *  Parse the file to an array.
     *
     *  @return array $this->parsed The parsed array
     *
     */

    public function toArray()
    {

        // Parse the file
        $this->parseFile();

        return (array) $this->parsed;
    }

    /**
     *
     *  Parse the file to an object.
     *
     *  @return obj $this->parsed The parsed object
     *
     */

    public function toObject()
    {
        // Parse the file
        $this->parseFile();

        return (object) json_decode(json_encode($this->parsed));
    }

    /**
     *
     *  Dump the parsed file to a readable array
     *
     *  @return array $this->parsed The parsed array
     *
     */

    public function dump()
    {

        // Parse the file
        $this->parseFile();

        echo '<pre class="container" style="background: #f5f5f5; border: 1px solid #e3e3e3; padding:15px;">';
            print_r($this->parsed);
        echo '</pre>';

    }

    /**
     *
     *  Init a new sheet
     *
     *  @param str $title The sheet name
     *  @param str $orientation The sheet orientation
     *  @return $this
     *
     */

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

    /**
     *
     *  Pass an array to the sheet to fill it with
     *
     *  @param array $array The array to fill the sheet with
     *  @return $this
     *
     */

    public function with($array)
    {

        // Send the variables to the excel sheet
        $this->excel
                ->getActiveSheet()
                    ->fromArray($array);

        return $this;
    }

    /**
     *
     *  Export the file to a given filetype
     *
     *  @param str $ext The file extension
     *  @return $this
     *
     */

    public function export($ext = 'xls')
    {

        // Set the extension
        $this->ext = $ext;

        // Render the XLS
        $this->render();

        // Set the headers
        $this->setHeaders();

        // Export the file
        $this->object->save('php://output');

        exit;
    }

    /**
     *
     *  Export the file to a given filetype
     *
     *  @param str $ext The file extension
     *  @param str $path The save path
     *  @return $this
     *
     */

    public function save($ext = 'xls', $path = false)
    {
        $this->store($ext, $path);
    }

    /**
     *
     *  Store the excel file to the server without a download popup
     *
     *  @param str $ext The file extension
     *  @param str $path The save path
     *  @return $this
     *
     */

    public function store($ext = 'xls', $path = false)
    {

        // Set the default path
        if($path == false)
        {
            $path = Config::get('excel::path');
        }

        // Trim of slashes, to makes sure we won't add them double.
        $path = rtrim($path, '/');
        $path = ltrim($path, '/');

        // Set the extension
        $this->ext = $ext;

        // Render the XLS
        $this->render();

        // Save the file to specified location
        $this->object->save('/' .$path . '/' . $this->title . '.' . $this->ext);
    }

    /**
     *
     *  Convert the file to a given filetype
     *
     *  @param str $ext The file extension
     *  @return $this
     *
     */

    public function convert($ext = 'xls')
    {

        // Parse the file
        $this->parseFile();

        // Reset the excel object
        $this->excel = new \PHPExcel();

        // Remove the default worksheet
        $this->excel->removeSheetByIndex(0);

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

    /**
     *
     *  Render the excel file
     *
     *  @return obj $this->object The writer
     *
     */

    private function render()
    {

        // Set the render format
        $this->format = $this->decodeFormat($this->ext);

        // Set to first sheet
        $this->excel->setActiveSheetIndex(0);

        // Create the writer
        return $this->object = \PHPExcel_IOFactory::createWriter($this->excel, $this->format);
    }

    /**
     *
     *  Parse the file
     *
     *  @return $this
     *
     */

    private function parseFile()
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
                if($this->firstRowAsLabel !== false)
                {
                    // Fetch the labels
                    $this->labels =  $this->getLabels();
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
            if($this->limit !== false)
            {
                $this->parsed = array_slice($parsed, $this->limit[1], $this->limit[0]);
            }
            else
            {
                $this->parsed = $parsed;
            }

        }

        $this->isParsed = true;

        // Return itself
        return $this;
    }

    /**
     *
     *  Get the labels
     *
     *  @return $this
     *
     */

    private function getLabels()
    {

         // Fetch the first row
        $this->row = $this->worksheet->getRowIterator(1)->current();

        // Set empty labels array
        $this->labels = array();

        // Loop through the cells
        foreach ($this->row->getCellIterator() as $this->cell) {

            // If format is CSV
            if($this->format == 'CSV')
            {
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

                    break;

                }
            }
            else
            {
                // Set labels
                $this->labels[] = str_replace(' ', '-',strtolower($this->cell->getValue()));
            }

        }

        // Return the labels
        return $this->labels;
    }

    /**
     *
     *  Parse the rows
     *
     *  @return $this
     *
     */

    private function parseRows()
    {

        // Set row index to 0

        $this->r = 0;

        // Set empty parsedRow array
        $parsedRow = array();

        // If the first row is the label, ignore the first row
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

    private function parseCells()
    {

        $i = 0;
        $parsedCells = array();

        // Set the cell iterator
        $this->cellIterator = $this->row->getCellIterator();
        $this->cellIterator->setIterateOnlyExistingCells($this->ignoreEmpty);

        // Foreach cells
        foreach ($this->cellIterator as $this->cell) {

            if($this->format == 'CSV')
            {
                // Parse the CSV cell
                $parsedCells = $this->parseCSVCell();

                // break to prevent empty rows and cells
                break;
            }
            else
            {

                // Get the cell index
                $index = \PHPExcel_Cell::columnIndexFromString($this->cell->getColumn());

                // Check how we need to save the parsed array
                if($this->firstRowAsLabel !== false)
                {
                    // Set label index
                    $index = $this->labels[$i];
                }

                // If the cell is a date time and we want to parse them
                if($this->formatDates !== false && PHPExcel_Shared_Date::isDateTime($this->cell))
                {
                    // Convert excel time to php date object
                    $value = PHPExcel_Shared_Date::ExcelToPHPObject($this->cell->getCalculatedValue());

                    // Format the date
                    $value = $value->format($this->dateFormat);

                    if($this->useCarbon)
                    {
                        $value = \Carbon::parse($value)->{$this->carbonMethod}();
                    }
                }

                // Check if we want calculated values or not
                elseif($this->calculate !== false)
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
        return $parsedCells;

    }

    /**
     *
     *  Parse the CSV cell
     *
     *  @return $this
     *
     */

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
                // Set label index
                $index = $this->labels[$i];
            }
            else
            {
                // Set i as index
                $index = $i;
            }

            // Set parsed array
            $parsedCSV[$index] = $newCell;

            $i++;
        }

        return $parsedCSV;
    }

    private function setHeaders()
    {
        // Set the headers
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->title . '.'. $this->ext .'"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
    }

    /**
     *
     *  Decode the format from extension
     *
     *  @param str $ext The file extension
     *  @return str The format;
     *
     */

    private function decodeFormat($ext = false)
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
        $this->excel->getActiveSheet()->freezePane($pane);
        return $this;
    }
	
	/**
     *
     *  Set a range of cell borders
     *
     *  @param string $pane Start and end of the cell (A1:F10)
	 *  @param string $weight Border style (Reference setBorder style list)
     *  @return $this
     *
     *  @author xiehai
     *  @example ->setBorder('A1:F10','thick')
     *
     */
	
	public function setBorder($pane = '', $weight = 'thin')
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
		
		$this->excel->getActiveSheet()
					->getStyle($pane)
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
     *
     *  @author xiehai
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
		
        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
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
}