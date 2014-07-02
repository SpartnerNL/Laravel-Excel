<?php namespace Maatwebsite\Excel\Readers;

use Cache;
use Config;
use PHPExcel_IOFactory;
use Illuminate\Filesystem\Filesystem;
use Maatwebsite\Excel\Parsers\ExcelParser;
use Maatwebsite\Excel\Classes\FormatIdentifier;
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
     * @var PHPExcel
     */
    public $excel;

    /**
     * Spreadsheet writer
     * @var object
     */
    public $reader;

    /**
     * The file to read
     * @var string
     */
    public $file;

    /**
     * Selected columns
     * @var array
     */
    public $columns = array();

    /**
     * Spreadsheet title
     * @var string
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
     * @var stirng
     */
    public $format;

    /**
     * The parsed file
     * @var SheetCollection|RowCollection
     */
    public $parsed;

    /**
     * Delimiter
     * @var string
     */
    public $delimiter;

    /**
     * Calculate [true/false]
     * @var boolean
     */
    public $calculate;

    /**
     * Limit data
     * @var boolean
     */
    protected $limit = false;

    /**
     * Amount of rows to skip
     * @var integer
     */
    protected $skip = 0;

    /**
     * Slug separator
     * @var string
     */
    public $separator = false;

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
     * Selected sheet indices
     * @var array
     */
    public $selectedSheetIndices = array();

    /**
     * Construct new reader
     * @param FileSystem $files
     * @param FormatIdentifier $identifier
     */
    public function __construct(Filesystem $filesystem, FormatIdentifier $identifier)
    {
        $this->filesystem = $filesystem;
        $this->identifier = $identifier;
    }

    /**
     * Load a file
     * @param  string  $file
     * @param  boolean $firstRowAsIndex
     * @return LaravelExcelReader
     */
    public function load($file, $encoding = false)
    {
        // init the loading
        $this->_init($file, $encoding);

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
     * @param array $sheets
     */
    public function setSelectedSheets($sheets)
    {
        $this->selectedSheets = $sheets;
    }

    /**
     * Check if sheets were selected
     * @return integer
     */
    public function sheetsSelected()
    {
        return count($this->selectedSheets) > 0;
    }

    /**
     * Check if the file was selected by index
     * @param  [type]  $index [description]
     * @return boolean        [description]
     */
    public function isSelectedByIndex($index)
    {
        $selectedSheets = $this->getSelectedSheetIndices();
        if(empty($selectedSheets)) return true;
        return in_array($index, $selectedSheets) ? true : false;
    }

    /**
     * Set the selected sheet indices
     * @param [type] $sheets [description]
     */
    public function setSelectedSheetIndices($sheets)
    {
        $this->selectedSheetIndices = $sheets;
        return $this;
    }

    /**
     * Return the selected sheets
     * @return [type] [description]
     */
    public function getSelectedSheetIndices()
    {
        return $this->selectedSheetIndices;
    }

    /**
     * Remember the results for x minutes
     * @param  integer $minutes
     * @return LaravelExcelReader
     */
    public function remember($minutes)
    {
        $this->remembered = true;
        $this->cacheMinutes = $minutes;
        return $this;
    }

    /**
     * Read the file through a config file
     * @param  stirng  $config
     * @param  callback|null $callback
     * @return SheetCollection
     */
    public function byConfig($config, $callback = null)
    {
        $config = new ConfigReader($this->excel, $config, $callback);
        return $config->getSheetCollection();
    }

    /**
     * Take x rows
     * @param  integer $amount
     * @return LaravelExcelReader
     */
    public function take($amount)
    {
        // Set limit
        $this->limit = $amount;
        return $this;
    }

    /**
     * Skip x rows
     * @param  integer $amount
     * @return LaravelExcelReader
     */
    public function skip($amount)
    {
        // Set skip amount
        $this->skip = $amount;
        return $this;
    }

    /**
     * Limit the results by x
     * @param  integer $take
     * @param  integer $skip
     * @return LaravelExcelReader
     */
    public function limit($take, $skip = 0)
    {
        // Skip x records
        $this->skip($skip);

        // Take x records
        $this->take($take);

        return $this;
    }

    /**
     * Select certain columns
     * @param  array  $columns
     * @return LaravelExcelReader
     */
    public function select($columns = array())
    {
        $this->columns = array_merge($this->columns, $columns);
        return $this;
    }

    /**
     * Return all sheets/rows
     * @param  array $columns
     * @return LaravelExcelReader
     */
    public function all($columns = array())
    {
        return $this->get($columns);
    }

    /**
     * Get first row/sheet only
     * @param  array $columns
     * @return SheetCollection|RowCollection
     */
    public function first($columns = array())
    {
        return $this->take(1)->get($columns)->first();
    }

    /**
     * Get all sheets/rows
     * @param array $columns
     * @return SheetCollection|RowCollection
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
     * @param  callback $callback
     * @return SheetCollection|RowCollection
     */
    public function each($callback)
    {
        return $this->get()->each($callback);
    }

    /**
     *  Parse the file to an array.
     *  @param  array  $columns
     *  @return array
     */
    public function toArray($columns = array())
    {
        return (array) $this->get($columns)->toArray();
    }

    /**
     *  Parse the file to an object.
     * @param array $columns
     * @return SheetCollection|RowCollection
     */
    public function toObject($columns = array())
    {
        return $this->get($columns);
    }

    /**
     *  Dump the parsed file to a readable array
     *  @param  array $columns
     *  @param  boolean $die
     */
    public function dump($columns = array(), $die = false)
    {
        echo '<pre class="container" style="background: #f5f5f5; border: 1px solid #e3e3e3; padding:15px;">';
            $die ? dd($this->get($columns)) : var_dump($this->get($columns));
        echo '</pre>';
    }

    /**
     * Die and dump
     * @param array $columns
     */
    public function dd($columns = array())
    {
        return $this->dump($columns, true);
    }

    /**
     * Init the loading
     * @param  string $file
     * @param  string|boolean $firstRowAsIndex
     * @return void
     */
    protected function _init($file, $encoding = false)
    {
        // Set the extension
        $this->_setFile($file)
              ->setExtension()
              ->setTitle()
              ->_setFormat()
              ->_setReader()
              ->_setInputEncoding($encoding);
    }

    /**
     * Inject the excel object
     * @param  PHPExcel $excel
     * @return void
     */
    public function injectExcel($excel)
    {
        $this->excel = $excel;
        $this->_reset();
    }

    /**
     * Set the file
     * @param string $file
     * @return  LaraveExcelReader
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
     * @param string|boolean $title
     * @return LaraveExcelReader
     */
    public function setTitle($title = false)
    {
        $this->title = $title ? $title : basename($this->file, '.' . $this->ext);
        return $this;
    }

    /**
     * Set extension
     * @param string|boolean $ext
     * @return LaraveExcelReader
     */
    public function setExtension($ext = false)
    {
        $this->ext = $ext ? $ext: $this->filesystem->extension($this->file);
        return $this;
    }

    /**
     * Set the date format
     * @param string $format The date format
     * @return LaraveExcelReader
     */
    public function setDateFormat($format = false)
    {
        $this->formatDates = $format ? true : false;
        $this->dateFormat = $format;
        return $this;
    }

    /**
     * Enable/disable date formating
     * @param  boolean $boolean True/false
     * @param  boolean $format
     * @return LaraveExcelReader
     */
    public function formatDates($boolean = true, $format = false)
    {
        $this->formatDates = $boolean;
        $this->setDateFormat($format);
        return $this;
    }

    /**
     * Set the date columns
     * @return LaraveExcelReader
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
     * @param  boolean $boolean
     * @return LaraveExcelReader
     */
    public function noHeading($boolean = true)
    {
        $this->noHeading = $boolean;
        return $this;
    }

    /**
     * Set the cell name word separator
     * @param string $separator
     * @return LaraveExcelReader
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * Spelling mistake backwards compatibility
     * @param [type] $separator [description]
     */
    public function setSeperator($separator)
    {
        return $this->setSeparator($separator);
    }

    /**
     * Set the delimiter
     * Calling this after the ->load() will have no effect
     * @param  string $delimiter
     * @return LaraveExcelReader
     */
    public function setDelimiter($delimiter)
    {
        $this->reader->setDelimiter($delimiter);
        return $this;
    }

    /**
     *  Set default calculate
     *  @param bool $boolean Calculate yes or no
     *  @return LaraveExcelReader
     */
    public function calculate($boolean = true)
    {
        $this->calculate = $boolean;
        return $this;
    }

    /**
     * Ignore empty cells
     * @param  boolean $boolean
     * @return LaraveExcelReader
     */
    public function ignoreEmpty($boolean = true)
    {
        $this->ignoreEmpty = $boolean;
        return $this;
    }

    /**
     * Check if the file has een heading
     * @return boolean
     */
    public function hasHeading()
    {
        if(!$this->noHeading)
        {
            $config = Config::get('excel::import.heading', true);
            return $config !== false && $config !== 'numeric';
        }

        return $this->noHeading ? false : true;
    }

    /**
     * Get the separator
     * @return string
     */
    public function getSeparator()
    {
        if($this->separator)
            return $this->separator;

        return Config::get('excel::import.separator', Config::get('excel::import.seperator', '_'));
    }

    /**
     * Get the dateFormat
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Get the date columns
     * @return array
     */
    public function getDateColumns()
    {
        return $this->dateColumns;
    }

    /**
     * Check if we need to calculate the formula inside the cell
     * @return boolean
     */
    public function needsCalculation()
    {
        return $this->calculate;
    }

    /**
     * Check if we need to ignore the empty cells
     * @return boolean
     */
    public function needsIgnoreEmpty()
    {
        return $this->ignoreEmpty;
    }

    /**
     * Check if we need to format the dates
     * @return boolean
     */
    public function needsDateFormatting()
    {
        return $this->formatDates ? true : false;
    }

    /**
     * Return the amount of rows to skip
     * @return integer
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * Return the amount of rows to take
     * @return integer
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the write format
     * @return LaraveExcelReader
     */
    protected function _setFormat()
    {
        $this->format = $this->identifier->getFormatByFile($this->file);
        return $this;
    }

    /**
     * Parse the file
     * @param  array $columns
     * @return void
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
     * @return LaraveExcelReader
     */
    protected function _setReader()
    {
        // Init the reader
        $this->reader = PHPExcel_IOFactory::createReader($this->format);
        $this->_setReaderDefaults();
        return $this;
    }

    /**
     * Set the input encoding
     * @param boolean $encoding
     * @return LaraveExcelReader
     */
    protected function _setInputEncoding($encoding = false)
    {
        if($this->format == 'CSV')
        {
            // If no encoding was given, use the config value
            $encoding = $encoding ? $encoding : Config::get('excel::import.encoding.input', 'UTF-8');
            $this->reader->setInputEncoding($encoding);
        }

        return $this;
    }

    /**
     * Set reader defaults
     * @return void
     */
    protected function _setReaderDefaults()
    {
        // Set CSV delimiter
        if($this->format == 'CSV')
        {
            $this->reader->setDelimiter(Config::get('excel::csv.delimiter', ','));
            $this->reader->setEnclosure(Config::get('excel::csv.enclosure', '"'));
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
     * @return void
     */
    protected function _reset()
    {
        $this->excel->disconnectWorksheets();
    }

    /**
     * Get excel object
     * @return PHPExcel
     */
    public function getExcel()
    {
        return $this->excel;
    }

    /**
     * Dynamically call methods
     * @param  string $method
     * @param  array $params
     * @throws LaravelExcelException
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