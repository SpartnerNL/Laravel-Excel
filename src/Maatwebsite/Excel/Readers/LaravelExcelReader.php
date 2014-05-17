<?php namespace Maatwebsite\Excel\Readers;

use \Cache;
use \Config;
use \PHPExcel_IOFactory;
use Illuminate\Filesystem\Filesystem;
use Maatwebsite\Excel\Parsers\ExcelParser;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

/**
 *
 * LaravelExcel Excel reader
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class LaravelExcelReader {

    /**
     * Excel object
     * @var [type]
     */
    public $excel;

    /**
     * Spreadsheet writer
     * @var [type]
     */
    public $reader;

    /**
     * The file to read
     * @var [type]
     */
    public $file;

    /**
     * Selected columns
     * @var array
     */
    public $columns = array();

    /**
     * Spreadsheet title
     * @var [type]
     */
    public $title;

    /**
     * Default extension
     * @var string
     */
    public $ext = 'xls';

    /**
     * Encoding
     * @var boolean
     */
    public $encoding = false;

    /**
     * Default format
     * @var [type]
     */
    public $format;

    /**
     * The parsed file
     * @var [type]
     */
    public $parsed;

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
    public $seperator = false;

     /**
     * Ignore empty cells
     * @var boolean
     */
    public $ignoreEmpty = false;

    /**
     * Format dates
     * @var boolean
     */
    public $formatDates = true;

    /**
     * The date columns
     * @var array
     */
    public $dateColumns = array();

    /**
     * If the file has a heading or not
     * @var boolean
     */
    public $noHeading = false;

    /**
     * Default date format
     * @var string
     */
    public $dateFormat;

    /**
     * Whether the results are cached or not
     * @var boolean
     */
    public $remembered = false;

    /**
     * Amount of minutes the results will remain cached
     * @var integer
     */
    public $cacheMinutes = 10;

    /**
     * Selected sheets
     * @var array
     */
    public $selectedSheets = array();

    /**
     * Construct new writer
     * @param Response   $response [description]
     * @param FileSystem $files    [description]
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Load a file
     * @param  [type]  $file            [description]
     * @param  boolean $firstRowAsIndex [description]
     * @param  string  $inputEncoding   [description]
     * @return [type]                   [description]
     */
    public function load($file)
    {
        // init the loading
        $this->_init($file);

        // Only fetch selected sheets if necessary
        if($this->sheetsSelected())
            $this->reader->setLoadSheetsOnly($this->selectedSheets);

        // Load the file
        $this->excel = $this->reader->load($this->file);

        // Return itself
        return $this;
    }

    /**
     * set selected sheets
     * @param [type] $sheets [description]
     */
    public function setSelectedSheets($sheets)
    {
        $this->selectedSheets = $sheets;
    }

    /**
     * Check if sheets were selected
     * @return [type] [description]
     */
    public function sheetsSelected()
    {
        return count($this->selectedSheets) > 0;
    }

    /**
     * Remember the results for x minutes
     * @param  [type] $minutes [description]
     * @return [type]          [description]
     */
    public function remember($minutes)
    {
        $this->remembered = true;
        $this->cacheMinutes = $minutes;
        return $this;
    }

    /**
     * Read the file through a config file
     * @param  [type]  $config   [description]
     * @param  boolean $callback [description]
     * @return [type]            [description]
     */
    public function byConfig($config, $callback = false)
    {
        $config = new ConfigReader($this->excel, $config, $callback);
        return $config->getSheetCollection();
    }

    /**
     * Take x rows
     * @param  [type] $amount [description]
     * @return [type]         [description]
     */
    public function take($amount)
    {
        $this->limit = $amount;
        return $this;
    }

    /**
     * Limit the results by x
     * @param  [type] $amount [description]
     * @return [type]         [description]
     */
    public function limit($amount)
    {
        return $this->take($amount);
    }

    // TODO: make a ->skip() method

    /**
     * Select certain columns
     * @param  array  $columns [description]
     * @return [type]          [description]
     */
    public function select($columns = array())
    {
        $this->columns = array_merge($this->columns, $columns);
        return $this;
    }

    /**
     * Return all sheets/rows
     * @return [type] [description]
     */
    public function all($columns = array())
    {
        return $this->get($columns);
    }

    /**
     * Get first row/sheet only
     * @return [type] [description]
     */
    public function first($columns = array())
    {
        return $this->take(1)->get($columns)->first();
    }

    /**
     * Get all sheets/rows
     * @return [type] [description]
     */
    public function get($columns = array())
    {
        if($this->remembered)
        {
            // Return cached results
            return Cache::remember(md5($this->file), $this->cacheMinutes, function() use (&$columns) {
                $this->_parseFile($columns);
                return $this->parsed;
            });
        }
        else
        {
            // return parsed file
            $this->_parseFile($columns);
            return $this->parsed;
        }
    }

    /**
     * Each
     * @param  [type] $callback [description]
     * @return [type]           [description]
     */
    public function each($callback)
    {
        return $this->get()->each($callback);
    }

    /**
     *
     *  Parse the file to an array.
     *
     *  @return array $this->parsed The parsed array
     *
     */
    public function toArray($columns = array())
    {
        return (array) $this->get($columns)->toArray();
    }

    /**
     *
     *  Parse the file to an object.
     *
     *  @return obj $this->parsed The parsed object
     *
     */
    public function toObject($columns = array())
    {
        return $this->get($columns);
    }

    /**
     *
     *  Dump the parsed file to a readable array
     *
     *  @return array $this->parsed The parsed array
     *
     */
    public function dump($columns = array(), $die = false)
    {
        echo '<pre class="container" style="background: #f5f5f5; border: 1px solid #e3e3e3; padding:15px;">';
            $die ? dd($this->get($columns)) : var_dump($this->get($columns));
        echo '</pre>';
    }

    /**
     * Die and dump
     * @return [type] [description]
     */
    public function dd($columns = array())
    {
        return $this->dump($columns, true);
    }

    /**
     * Init the loading
     * @param  [type] $file            [description]
     * @param  [type] $firstRowAsIndex [description]
     * @param  [type] $inputEncoding   [description]
     * @return [type]                  [description]
     */
    protected function _init($file)
    {
        // Set the extension
        $this->_setFile($file)
              ->setExtension()
              ->setTitle()
              ->_setFormat()
              ->_setReader();
    }

    /**
     * Inject the excel object
     * @param  [type] $excel [description]
     * @return [type]        [description]
     */
    public function injectExcel($excel)
    {
        $this->excel = $excel;
        $this->_reset();
    }

    /**
     * Set the file
     * @param [type] $file [description]
     */
    protected function _setFile($file)
    {
        // check if we have a correct path
        if(!realpath($file))
            $file = base_path($file);

        $this->file = $file;
        return $this;
    }

    /**
     * Set the spreadsheet title
     * @param [type] $title [description]
     */
    public function setTitle($title = false)
    {
        $this->title = $title ? $title : basename($this->file, '.' . $this->ext);
        return $this;
    }

    /**
     * Set extension
     * @param [type] $ext [description]
     */
    public function setExtension($ext = false)
    {
        $this->ext = $ext ? $ext: $this->filesystem->extension($this->file);
        return $this;
    }

    /**
     * Set the date format
     * @param str $format The date format
     */
    public function setDateFormat($format = false)
    {
        $this->formatDates = $format ? true : false;
        $this->dateFormat = $format;
        return $this;
    }

    /**
     * Enable/disable date formating
     * @param  bool $boolean True/false
     */
    public function formatDates($boolean = true, $format = false)
    {
        $this->formatDates = $boolean;
        $this->setDateFormat($format);
        return $this;
    }

    /**
     * Set the date columns
     */
    public function setDateColumns()
    {
        $this->formatDates = true;
        $columns = func_get_args();
        $this->dateColumns = array_merge($this->dateColumns, array_flatten($columns));
        return $this;
    }

    /**
     * If the file has a table heading or not
     * @param  [type] $boolean [description]
     * @return [type]          [description]
     */
    public function noHeading($boolean = true)
    {
        $this->noHeading = $boolean;
        return $this;
    }

    /**
     * Set the cell name word seperator
     * @param [type] $seperator [description]
     */
    public function setSeperator($seperator)
    {
        $this->seperator = $seperator;
        return $this;
    }

    /**
     * Set the delimiter
     * Calling this after the ->load() will have no effect
     */
    public function setDelimiter($delimiter)
    {
        $this->reader->setDelimiter($delimiter);
        return $this;
    }

    /**
     *
     *  Set default calculate
     *
     *  @param bool $boolean Calculate yes or no
     *  @return $this
     *
     */
    public function calculate($boolean = true)
    {
        $this->calculate = $boolean;
        return $this;
    }

    /**
     * Ignore empty cells
     * @param  boolean $boolean [description]
     * @return [type]           [description]
     */
    public function ignoreEmpty($boolean = true)
    {
        $this->ignoreEmpty = $boolean;
        return $this;
    }

    /**
     * Check if the file has een heading
     * @return boolean [description]
     */
    public function hasHeading()
    {
        if(!$this->noHeading)
            return Config::get('excel::import.heading', true);

        return $this->noHeading ? false : true;
    }

    /**
     * Get the seperator
     * @return [type] [description]
     */
    public function getSeperator()
    {
        if($this->seperator)
            return $this->seperator;

        return Config::get('excel::import.seperator', '_');
    }

    /**
     * Get the dateFormat
     * @return [type] [description]
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Get the date columns
     * @return [type] [description]
     */
    public function getDateColumns()
    {
        return $this->dateColumns;
    }

    /**
     * Check if we need to calculate the formula inside the cell
     * @return [type] [description]
     */
    public function needsCalculation()
    {
        return $this->calculate;
    }

    /**
     * Check if we need to ingore the empty cells
     * @return [type] [description]
     */
    public function needsIgnoreEmpty()
    {
        return $this->ignoreEmpty;
    }

    /**
     * Check if we need to format the dates
     * @return [type] [description]
     */
    public function needsDateFormatting()
    {
        return $this->formatDates ? true : false;
    }

    /**
     * Set the write format
     */
    protected function _setFormat()
    {
        $this->format = PHPExcel_IOFactory::identify($this->file);
        return $this;
    }

    /**
     * Parse the file
     * @return [type] [description]
     */
    protected function _parseFile($columns = array())
    {
        // Merge the selected columns
        $columns = array_merge($this->columns, $columns);

        // Parse the file
        $parser = new ExcelParser($this);
        $this->parsed = $parser->parseFile($columns);
    }

    /**
     * Set the writer
     */
    protected function _setReader()
    {
        // Init the reader
        $this->reader = PHPExcel_IOFactory::createReader($this->format);
        $this->_setReaderDefaults();
        return $this;
    }

    /**
     * Set reader defaults
     */
    protected function _setReaderDefaults()
    {
        // Set CSV delimiter
        if($this->format == 'CSV')
        {
            $this->reader->setDelimiter(Config::get('excel::csv.delimiter', ','));
            $this->reader->setInputEncoding(Config::get('excel::import.encoding.input', 'UTF-8'));
            $this->reader->setEnclosure(Config::get('excel::csv.enclosure', ''));
            $this->reader->setLineEnding(Config::get('excel::csv.line_ending', "\r\n"));
        }

        // Set default calculate
        $this->calculate = Config::get('excel::import.calculate', true);

        // Set default for ignoring empty cells
        $this->ignoreEmpty = Config::get('excel::import.ignoreEmpty', true);

        // Set default date format
        $this->dateFormat = Config::get('excel::import.dates.format', 'Y-m-d');

        // Date formatting disabled/enabled
        $this->formatDates = Config::get('excel::import.dates.enabled', true);

        // Set default date columns
        $this->dateColumns = Config::get('excel::import.dates.columns', array());
    }

    /**
     * Reset the writer
     * @return [type] [description]
     */
    protected function _reset()
    {
        $this->excel->disconnectWorksheets();
    }

    /**
     * Dynamically call methods
     * @param  [type] $method [description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function __call($method, $params)
    {
        // Call a php excel method
        if(method_exists($this->excel, $method))
        {
            // Call the method from the excel object with the given params
            return call_user_func_array(array($this->excel, $method), $params);
        }

        // If it's a reader method
        elseif(method_exists($this->reader, $method))
        {
            // Call the method from the excel object with the given params
            return call_user_func_array(array($this->reader, $method), $params);
        }

        throw new LaravelExcelException('[ERROR] Reader method ['. $method .'] does not exist.');

    }

}