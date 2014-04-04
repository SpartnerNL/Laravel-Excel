<?php namespace Maatwebsite\Excel;

use \PHPExcel;
use Carbon\Carbon;
use \PHPExcel_Cell;
use \PHPExcel_IOFactory;
use \PHPExcel_Shared_Date;
use Illuminate\Support\Str;
use \PHPExcel_Style_NumberFormat;
use \PHPExcel_Worksheet_PageSetup;
use Illuminate\View\Environment as View;
use Maatwebsite\Excel\Readers\HTML_reader;
use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem as File;

/**
 * Laravel wrapper for PHPEXcel
 *
 * @version 0.3.0
 * @package maatwebsite/excel
 * @author Maatwebsite <info@maatwebsite.nl>
 * @contributors Maatwebsite, mewben, hicode, lollypopgr, floptwo, jonwhittlestone, BoHolm
 */

class Excel extends PHPExcel
{
    /**
     * PHP Excel object
     * @var [type]
     */
    public $excel;

    /**
     * Writer object
     * @var [type]
     */
    protected $object;

    /**
     * Current sheet number
     * @var integer
     */
    public $i = 0;

    /**
     * File title
     * @var [type]
     */
    public $title;

    /**
     * File extension
     * @var [type]
     */
    public $ext;

    /**
     * Format
     * @var [type]
     */
    public $format;

    /**
     * Delimtier
     * @var [type]
     */
    public $delimiter;

    /**
     * Calculate [true/false]
     * @var [type]
     */
    public $calculate;

    /**
     * Limit data
     * @var boolean
     */
    public $limit = false;

    /**
     * Slug seperator
     * @var string
     */
    public $seperator = '-';

    /**
     * Loaded view
     * @var [type]
     */
    protected $view;

    /**
     * View data
     * @var array
     */
    protected $data = array();

    /**
     * View merge Data
     * @var [type]
     */
    protected $mergeData;

    /**
     * Ignore empty cells
     * @var boolean
     */
    protected $ignoreEmpty = false;

    /**
     * File is parsed
     * @var boolean
     */
    protected $isParsed = false;

    /**
     * Use first row as array indices
     * @var boolean
     */
    protected $firstRowAsLabel = false;

    /**
     * Format dates
     * @var boolean
     */
    protected $formatDates = true;

    /**
     * Default date format
     * @var string
     */
    protected $dateFormat = 'Y-m-d';

    /**
     * Use carbon to format dates
     * @var boolean
     */
    protected $useCarbon = false;

    /**
     * Default carbon method
     * @var string
     */
    protected $carbonMethod = 'toDateTimeString';

    /**
     *
     *  Constructor
     *
     *  Init the parent, init PHPExcel and set the defaults
     *
     */

    public function __construct(PHPExcel $excel, HTML_reader $htmlReader, Config $config, View $view, File $file)
    {
        parent::__construct();

        // Set dependencies
        $this->excel = $excel;
        $this->htmlReader = $htmlReader;
        $this->config = $config;
        $this->viewFactory = $view;
        $this->fileSystem = $file;

        // Set defaults
        $this->delimiter = $this->config->get('excel::delimiter', $this->delimiter);
        $this->calculate = $this->config->get('excel::calculate', $this->calculate);
        $this->ignoreEmpty = $this->config->get('excel::ignoreEmpty', $this->ignoreEmpty);
        $this->dateFormat = $this->config->get('excel::date_format', $this->dateFormat);
        $this->seperator = $this->config->get('excel::seperator', $this->seperator);

        // Reset
        $this->reset();

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
        // Reset
        $this->reset();

        // Set file title
        $this->title = $title;

        // Remove the default worksheet
        $this->excel->disconnectWorksheets();

        // Set properties
        $this->excel->getProperties()
                    ->setCreator($this->config->get('excel::creator'))
                    ->setLastModifiedBy($this->config->get('excel::creator'))
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
    public function load($file, $firstRowAsLabel = false, $inputEncoding = 'UTF-8')
    {
        // Reset
        $this->reset();

        // Set defaults
        $this->file = $file;
        $this->ext = $this->fileSystem->extension($this->file);
        $this->title = basename($this->file, '.' . $this->ext);
        $this->firstRowAsLabel = $firstRowAsLabel;

        // Identify the format
        $this->format = PHPExcel_IOFactory::identify($this->file);

        // Init the reader
        $this->reader = PHPExcel_IOFactory::createReader($this->format);

        if ($this->format === 'CSV')
            $this->reader->setInputEncoding($inputEncoding);

        // Load the file
        $this->excel = $this->reader->load($this->file);

        // Return itself
        return $this;
    }

    /**
     * Reload the reader
     * @return [type] [description]
     */
    public function reload()
    {
        $this->excel = $this->reader->load($this->file);
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

    /**
     * Use carbon to format dates
     * @param  boolean $method [description]
     * @return [type]          [description]
     */
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
    public function loadHTML($string)
    {
        $this->reader = $this->htmlReader;
        $this->excel = $this->reader->load($string, true);

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
    public function loadView($view, $data = array(), $mergeData = array())
    {
        // Reset
        $this->reset();

        $this->view = $view;
        $this->data = $data;
        $this->mergeData = $mergeData;
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
        $this->reader->setDelimiter($delimiter);
        $this->reload();
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
    public function setEnclosure($enclosure = '')
    {
        $this->reader->setEnclosure($enclosure);
        $this->reload();
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
    public function setLineEnding($lineEnding = "\r\n")
    {
        $this->reader->setLineEnding($lineEnding);
        $this->reload();
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
                $this->orientation = PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT;
                break;

            case 'landscape':
                $this->orientation = PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE;
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
                        ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4)
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
    public function with($key, $value = false)
    {
        // If key is an array, we are assigning data to a new excel file,
        // create a new active sheet from that array
        if(is_array($key))
        {
            // Send the variables to the excel sheet
            $this->excel
                    ->getActiveSheet()
                        ->fromArray($key);
        }
        else
        {
            return $this->addVars($key, $value);
        }

        return $this;
    }

    /**
     * Add vars to the data array
     * @param [type]  $key   [description]
     * @param boolean $value [description]
     */
    public function addVars($key, $value = false)
    {
        $this->data[$key] = $value;
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
        return $this->store($ext, $path);
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
    public function store($ext = 'xls', $path = false, $returnInfo = false)
    {

        // Set the default path
        if($path == false)
        {
            $path = $this->config->get('excel::path');
        }

        // Trim of slashes, to makes sure we won't add them double.
        $path = rtrim($path, '/');
        //$path = ltrim($path, '/');

        // Set the extension
        $this->ext = $ext;

        // Render the XLS
        $this->render();

        $toStore = $path . '/' . $this->title . '.' . $this->ext;

        // Save the file to specified location
        $this->object->save($toStore);


        if($returnInfo)
        {

            // Send back information about the stored file
            return array(
                'full'  => $toStore,
                'path'  => $path,
                'file'  => $this->title . '.' . $this->ext,
                'title' => $this->title,
                'ext'   => $this->ext
            );

        }

        return $this;

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
        $this->excel = app('phpexcel');

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
        // If a view was set, make that view
        if($this->view)
            $this->makeView();

        // Set the render format
        $this->format = $this->decodeFormat($this->ext);

        // Set to first sheet
        $this->excel->setActiveSheetIndex(0);

        // Create the writer
        return $this->object = PHPExcel_IOFactory::createWriter($this->excel, $this->format);
    }

    /**
     * Make the view and load the html
     * @return [type] [description]
     */
    protected function makeView()
    {
        // Make the view
        $html = $this->viewFactory->make($this->view, $this->data, $this->mergeData);

        // Load the html
        $this->loadHTML($html);
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

            // Set labels
            $this->labels[] = Str::slug($this->cell->getValue(), $this->seperator);

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

            // Get the cell index
            $index = PHPExcel_Cell::columnIndexFromString($this->cell->getColumn());

            // Check how we need to save the parsed array
            if($this->firstRowAsLabel !== false)
            {
                // Set label index
                $index = $this->labels[$i];
            }

            // If the cell is a date time
            if(PHPExcel_Shared_Date::isDateTime($this->cell))
            {

                // Check if we want to parse the dates
                if ($this->formatDates !== false)
                {

                    // Convert excel time to php date object
                    $value = PHPExcel_Shared_Date::ExcelToPHPObject($this->cell->getCalculatedValue());

                    // Format the date
                    $value = $value->format($this->dateFormat);

                    // Use carbon to parse the time
                    if($this->useCarbon)
                    {
                        $value = Carbon::parse($value)->{$this->carbonMethod}();
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

            $i++;

        }

        // Return array with parsed cells
        return $parsedCells;

    }

    /**
     * Set headers
     */
    private function setHeaders()
    {
        // Set the headers
        switch($this->ext)
        {
            case 'xlsx':
                header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                break;

            case 'csv':
                header('Content-type: application/csv');
                break;

            default: // xls
                header('Content-Type: application/vnd.ms-excel');
                break;
        }

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

             case 'xlsx':
                return 'Excel2007';
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
     *
     *  @author xiehai
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

        // Apply the style
        $this->excel->getDefaultStyle()
                    ->applyFromArray($styleArray);

		return $this;
	}

	/**
     *
     *  Set AutoFilter
     *
     *  @return $this
     *
     *  @author xiehai
     *  @example ->setAutoFilter()
     *
     */
    public function setAutoFilter(){
        $this->excel->getActiveSheet()
                    ->setAutoFilter($this->excel->getActiveSheet()
                    ->calculateWorksheetDimension());

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
	 * 			'B' => '0',
	 * 			'D' => '0.00',
	 * 			'F' => '@',
	 * 			'F' => 'yyyy-mm-dd',
	 * 			......
	 * 		)
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
			$this->excel->getActiveSheet()
        				->getStyle($column)
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
	 * 			'A' => '10',
	 * 			'B' => '22',
	 * 			'F' => '8',
	 * 			'N' => '13',
	 * 			......
	 * 		)
	 *  )
	 *
     */
    public function setColumnWidth(Array $pane)
    {

		foreach ($pane as $column => $width) {
			$this->excel->getActiveSheet()->getColumnDimension($column)->setWidth($width);
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
	 *  @example	$mergeColumn = array(
	 *		            'columns' => array('A','B','C','D'),
	 *		            'rows' => array(
	 *			 			array(2,3),
	 *			 			array(5,11),
	 * 						.....
	 *			 		 )
	 * 		      );
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
     *
     *  Native merged cell method
	 *
	 *  @return $this
	 *  @param array $cells
	 *
	 *  @author xiehai
     *
     */
	public function mergeCells($cells)
	{
        $this->excel->getActiveSheet()->mergeCells($cells);
        return $this;
    }

    /**
     * Reset the parsed state
     * @return [type] [description]
     */
    public function reset()
    {
        // Reset i back to zero
        $this->i = 0;

        // Reset parsed state
        $this->isParsed = false;

        return $this;
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
            $this->addVars($key, reset($params));
        }

        return $this;
    }

}