<?php namespace Maatwebsite\Excel\Writers;

use Closure;
use Carbon\Carbon;
use PHPExcel_IOFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Classes\FormatIdentifier;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

/**
 *
 * LaravelExcel Excel writer
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class LaravelExcelWriter {

    /**
     * Spreadsheet filename
     * @var string
     */
    public $filename;

    /**
     * Spreadsheet title
     * @var string
     */
    public $title;

    /**
     * Excel object
     * @var \PHPExcel
     */
    public $excel;

    /**
     * Laravel response
     * @var Response
     */
    protected $response;

    /**
     * Spreadsheet writer
     * @var object
     */
    public $writer;

    /**
     * Excel sheet
     * @var LaravelExcelWorksheet
     */
    protected $sheet;

    /**
     * Parser
     * @var ViewParser
     */
    public $parser;

    /**
     * Default extension
     * @var string
     */
    public $ext = 'xls';

    /**
     * Path the file will be stored to
     * @var string
     */
    public $storagePath = 'exports';

    /**
     * Header Content-type
     * @var string
     */
    protected $contentType;

    /**
     * Spreadsheet is rendered
     * @var boolean
     */
    protected $rendered = false;

    /**
     * Construct writer
     * @param Response         $response
     * @param FileSystem       $filesystem
     * @param FormatIdentifier $identifier
     */
    public function __construct(Response $response, FileSystem $filesystem, FormatIdentifier $identifier)
    {
        $this->response = $response;
        $this->filesystem = $filesystem;
        $this->identifier = $identifier;
    }

    /**
     * Inject the excel object
     * @param  PHPExcel $excel
     * @param bool      $reset
     * @return void
     */
    public function injectExcel($excel, $reset = true)
    {
        $this->excel = $excel;

        if ($reset)
            $this->_reset();
    }

    /**
     * Set the spreadsheet title
     * @param string $title
     * @return  LaravelExcelWriter
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->getProperties()->setTitle($title);

        return $this;
    }

    /**
     * Set the filename
     * @param  $name
     * @return $this
     */
    public function setFileName($name)
    {
        $this->filename = $name;

        return $this;
    }

    /**
     * Get the title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the title
     * @return string
     */
    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * Share view with all sheets
     * @param  string $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return  LaravelExcelWriter
     */
    public function shareView($view, $data = array(), $mergeData = array())
    {
        // Get the parser
        $this->getParser();

        // Set the view inside the parser
        $this->parser->setView($view);
        $this->parser->setData($data);
        $this->parser->setMergeData($mergeData);

        return $this;
    }

    /**
     * Set the view
     * @return  LaravelExcelWriter
     */
    public function setView()
    {
        return call_user_func_array(array($this, 'shareView'), func_get_args());
    }

    /**
     * Load the view
     * @return  LaravelExcelWriter
     */
    public function loadView()
    {
        return call_user_func_array(array($this, 'shareView'), func_get_args());
    }

    /**
     * Create a new sheet
     * @param  string        $title
     * @param  callback|null $callback
     * @return  LaravelExcelWriter
     */
    public function sheet($title, $callback = null)
    {
        // Clone the active sheet
        $this->sheet = $this->excel->createSheet(null, $title);

        // If a parser was set, inject it
        if ($this->parser)
            $this->sheet->setParser($this->parser);

        // Set the sheet title
        $this->sheet->setTitle($title);

        // Set the default page setup
        $this->sheet->setDefaultPageSetup();

        // Do the callback
        if ($callback instanceof Closure)
            call_user_func($callback, $this->sheet);

        // Autosize columns when no user didn't change anything about column sizing
        if (!$this->sheet->hasFixedSizeColumns())
            $this->sheet->setAutosize(Config::get('excel.export.autosize', false));

        // Parse the sheet
        $this->sheet->parsed();

        return $this;
    }

    /**
     * Set data for the current sheet
     * @param  array $array
     * @return  LaravelExcelWriter
     */
    public function with(Array $array)
    {
        // Add the vars
        $this->fromArray($array);

        return $this;
    }

    /**
     * Export the spreadsheet
     * @param string $ext
     * @param array  $headers
     * @throws LaravelExcelException
     */
    public function export($ext = 'xls', Array $headers = array())
    {
        // Set the extension
        $this->ext = $ext;

        // Render the file
        $this->_render();

        // Download the file
        $this->_download($headers);
    }

    /**
     * Convert and existing file to newly requested extension
     * @param       $ext
     * @param array $headers
     */
    public function convert($ext, Array $headers = array())
    {
        $this->export($ext, $headers);
    }

    /**
     * Export and download the spreadsheet
     * @param  string $ext
     * @param array   $headers
     */
    public function download($ext = 'xls', Array $headers = array())
    {
        $this->export($ext, $headers);
    }

    /**
     * Return the spreadsheet file as a string
     * @param  string $ext
     * @return string
     * @throws LaravelExcelException
     */
    public function string($ext = 'xls')
    {
        // Set the extension
        $this->ext = $ext;

        // Render the file
        $this->_render();

        // Check if writer isset
        if (!$this->writer)
            throw new LaravelExcelException('[ERROR] No writer was set.');

        //Capture the content as a string and return it
        ob_start();

        $this->writer->save('php://output');

        return ob_get_clean();
    }

    /**
     * Download a file
     * @param array $headers
     * @throws LaravelExcelException
     */
    protected function _download(Array $headers = array())
    {
        // Set the headers
        $this->_setHeaders(
            $headers,
            array(
                'Content-Type'        => $this->contentType,
                'Content-Disposition' => 'attachment; filename="' . $this->filename . '.' . $this->ext . '"',
                'Expires'             => 'Mon, 26 Jul 1997 05:00:00 GMT', // Date in the past
                'Last-Modified'       => Carbon::now()->format('D, d M Y H:i:s'),
                'Cache-Control'       => 'cache, must-revalidate',
                'Pragma'              => 'public'
            )
        );

        // Check if writer isset
        if (!$this->writer)
            throw new LaravelExcelException('[ERROR] No writer was set.');


        // Download
        $this->writer->save('php://output');

        // End the script to prevent corrupted xlsx files
        exit;
    }

    /**
     * Store the excel file to the server
     * @param  string  $ext
     * @param  boolean $path
     * @param  boolean $returnInfo
     * @return LaravelExcelWriter
     */
    public function store($ext = 'xls', $path = false, $returnInfo = false)
    {
        // Set the storage path
        $this->_setStoragePath($path);

        // Set the extension
        $this->ext = $ext;

        // Render the XLS
        $this->_render();

        // Set the storage path and file
        $toStore = $this->storagePath . '/' . $this->filename . '.' . $this->ext;

        // Save the file to specified location
        $this->writer->save($toStore);

        // Return file info
        if ($this->returnInfo($returnInfo))
        {
            // Send back information about the stored file
            return array(
                'full'  => $toStore,
                'path'  => $this->storagePath,
                'file'  => $this->filename . '.' . $this->ext,
                'title' => $this->filename,
                'ext'   => $this->ext
            );
        }

        // Return itself
        return $this;
    }

    /**
     * Check if we want to return info or itself
     * @param  boolean $returnInfo
     * @return boolean
     */
    public function returnInfo($returnInfo = false)
    {
        return $returnInfo ? $returnInfo : Config::get('excel.export.store.returnInfo', false);
    }

    /**
     *  Store the excel file to the server
     * @param str|string $ext  The file extension
     * @param bool|str   $path The save path
     * @param bool       $returnInfo
     * @return LaravelExcelWriter
     */
    public function save($ext = 'xls', $path = false, $returnInfo = false)
    {
        return $this->store($ext, $path, $returnInfo);
    }

    /**
     * Start render of a new spreadsheet
     * @throws LaravelExcelException
     * @return void
     */
    protected function _render()
    {
        // There should be enough sheets to continue rendering
        if ($this->excel->getSheetCount() < 0)
            throw new LaravelExcelException('[ERROR] Aborting spreadsheet render: no sheets were created.');

        // Set the format
        $this->_setFormat();

        // Set the writer
        $this->_setWriter();

        // File has been rendered
        $this->rendered = true;
    }

    /**
     * Get the view parser
     * @return PHPExcel
     */
    public function getExcel()
    {
        return $this->excel;
    }

    /**
     * Get the view parser
     * @return ViewParser
     */
    public function getParser()
    {
        // Init the parser
        if (!$this->parser)
            $this->parser = app('excel.parsers.view');

        return $this->parser;
    }

    /**
     * Get the sheet
     * @return LaravelExcelWorksheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * Set attributes
     * @param string $setter
     * @param array  $params
     */
    protected function _setAttribute($setter, $params)
    {
        // Get the key
        $key = lcfirst(str_replace('set', '', $setter));

        // If is an allowed property
        if ($this->excel->isChangeableProperty($setter))
        {
            // Set the properties
            call_user_func_array(array($this->excel->getProperties(), $setter), $params);
        }
    }

    /**
     * Set the write format
     * @return  void
     */
    protected function _setFormat()
    {
        // Get extension
        $this->ext = strtolower($this->ext);

        // get the file format
        $this->format = $this->identifier->getFormatByExtension($this->ext);

        // Get content type
        $this->contentType = $this->identifier->getContentTypeByFormat($this->format);
    }

    /**
     * Set the writer
     * @return PHPExcel_***_Writer
     */
    protected function _setWriter()
    {
        // Set pdf renderer
        if ($this->format == 'PDF')
        {
            $this->setPdfRenderer();
        }

        // Create the writer
        $this->writer = PHPExcel_IOFactory::createWriter($this->excel, $this->format);

        // Set CSV delimiter
        if ($this->format == 'CSV')
        {
            $this->writer->setDelimiter(Config::get('excel.csv.delimiter', ','));
            $this->writer->setEnclosure(Config::get('excel.csv.enclosure', '"'));
            $this->writer->setLineEnding(Config::get('excel::csv.line_ending', "\r\n"));
        }

        // Set CSV delimiter
        if ($this->format == 'PDF')
        {
            $this->writer->writeAllSheets();
        }

        // Calculation settings
        $this->writer->setPreCalculateFormulas(Config::get('excel.export.calculate', false));

        // Include Charts
        $this->writer->setIncludeCharts(Config::get('excel.export.includeCharts', false));

        return $this->writer;
    }

    /**
     * Set the pdf renderer
     * @throws \Exception
     */
    protected function setPdfRenderer()
    {
        // Get the driver name
        $driver = Config::get('excel.export.pdf.driver');
        $path = Config::get('excel.export.pdf.drivers.' . $driver . '.path');

        // Disable autoloading for dompdf
        if(! defined("DOMPDF_ENABLE_AUTOLOAD")){
            define("DOMPDF_ENABLE_AUTOLOAD", false);
        }

        // Set the pdf renderer
        if (!\PHPExcel_Settings::setPdfRenderer($driver, $path))
            throw new \Exception("{$driver} could not be found. Make sure you've included it in your composer.json");
    }

    /**
     * Set the headers
     * @param $headers
     * @throws LaravelExcelException
     */
    protected function _setHeaders(Array $headers = array(), Array $default)
    {
        if (headers_sent()) throw new LaravelExcelException('[ERROR]: Headers already sent');

        // Merge the default headers with the overruled headers
        $headers = array_merge($default, $headers);

        foreach ($headers as $header => $value)
        {
            header($header . ': ' . $value);
        }
    }

    /**
     * Set the storage path
     * @param bool $path
     * @return  void
     */
    protected function _setStoragePath($path = false)
    {
        // Get the default path
        $path = $path ? $path : Config::get('excel.export.store.path', storage_path($this->storagePath));

        // Trim of slashes, to makes sure we won't add them double
        $this->storagePath = rtrim($path, '/');

        // Make sure the storage path exists
        if (!$this->filesystem->isWritable($this->storagePath))
            $this->filesystem->makeDirectory($this->storagePath, 0777, true);
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
     * Dynamically call methods
     * @param  string $method
     * @param  array  $params
     * @throws LaravelExcelException
     * @return LaravelExcelWriter
     */
    public function __call($method, $params)
    {
        // If the dynamic call starts with "set"
        if (starts_with($method, 'set') && $this->excel->isChangeableProperty($method))
        {
            $this->_setAttribute($method, $params);

            return $this;
        }

        // Call a php excel method
        elseif (method_exists($this->excel, $method))
        {
            // Call the method from the excel object with the given params
            $return = call_user_func_array(array($this->excel, $method), $params);

            return $return ? $return : $this;
        }

        throw new LaravelExcelException('[ERROR] Writer method [' . $method . '] does not exist.');
    }
}
