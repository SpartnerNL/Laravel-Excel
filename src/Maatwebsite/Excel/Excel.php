<?php namespace Maatwebsite\Excel;

use \Closure;
use \PHPExcel;
use Carbon\Carbon;
use \PHPExcel_Cell;
use \PHPExcel_IOFactory;
use \PHPExcel_Shared_Date;
use Illuminate\Support\Str;
use \PHPExcel_Style_NumberFormat;
use \PHPExcel_Worksheet_PageSetup;
use Maatwebsite\Excel\Readers\Batch;
use Illuminate\View\Environment as View;
use Maatwebsite\Excel\Readers\HTML_reader;
use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem as File;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Maatwebsite\Excel\Parsers\ViewParser;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

/**
 * Laravel wrapper for PHPEXcel
 *
 * @version 0.4.0
 * @package maatwebsite/excel
 * @author Maatwebsite <info@maatwebsite.nl>
 */

class Excel
{
    /**
     * Excel object
     * @var [type]
     */
    public $excel;

    /**
     * Batch object
     * @var [type]
     */
    public $batch;

    /**
     * Html reader
     * @var [type]
     */
    protected $htmlReader;

    /**
     * Config repository
     * @var [type]
     */
    protected $config;

    /**
     * View factory
     * @var [type]
     */
    protected $viewFactory;

    /**
     * File system
     * @var [type]
     */
    protected $fileSystem;

    /**
     * Construct Excel
     * @param PHPExcel    $excel      [description]
     * @param HTML_reader $htmlReader [description]
     * @param Config      $config     [description]
     * @param View        $view       [description]
     * @param File        $file       [description]
     */
    public function __construct(PHPExcel $excel, LaravelExcelReader $reader, LaravelExcelWriter $writer, ViewParser $parser, Config $config, View $view, File $file)
    {
        // Set Excel dependencies
        $this->excel = $excel;
        $this->reader = $reader;
        $this->writer = $writer;
        $this->parser = $parser;

        // Set Laravel classes
        $this->config = $config;
        $this->viewFactory = $view;
        $this->fileSystem = $file;
    }

    /**
     * Create a new file
     * @param  [type] $title [description]
     * @return [type]        [description]
     */
    public function create($title, $callback = false)
    {
        // Set the default properties
        $this->excel->setDefaultProperties(array(
            'title' => $title
        ));

        $this->excel->disconnectWorksheets();

        // Inject our excel object
        $this->writer->injectExcel($this->excel);

        // Set the title
        $this->writer->setTitle($title);

        // Do the callback
        if($callback instanceof Closure)
            call_user_func($callback, $this->writer);

        // Return the writer object
        return $this->writer;
    }

    /**
     *
     *  Load an existing file
     *
     *  @param str $file The file we want to load
     *  @param bool $firstRowAsIndex Do we want to interpret de first row as labels?
     *  @return $this
     *
     */
    public function load($file, $callback = false)
    {
        // Inject excel object
        $this->reader->injectExcel($this->excel);

        // Start loading
        $this->reader->load($file);

        // Do the callback
        if($callback instanceof Closure)
            call_user_func($callback, $this->reader);

        // Return the reader object
        return $this->reader;
    }

    /**
     * Set select sheets
     * @param  [type] $sheets [description]
     * @return [type]         [description]
     */
    public function selectSheets($sheets)
    {
        $this->reader->setSelectedSheets(is_array($sheets) ? $sheets : array($sheets));
        return $this;
    }

    /**
     * Batch import
     * @return [type] [description]
     */
    public function batch($files, Closure $callback)
    {
        return new Batch($this, $files, $callback);
    }

    /**
     * Create a new file and share a view
     * @return [type] [description]
     */
    public function shareView($view, $data = array(), $mergeData = array())
    {
        return $this->create('New file')->shareView($view, $data, $mergeData);
    }

    /**
     * Create a new file and load a view
     * [NOT RECOMMENDED TO USE; ONLY FOR BACKWARDS COMPATABILITY]
     * @return [type] [description]
     */
    public function loadView($view, $data = array(), $mergeData = array())
    {
        return $this->shareView($view, $data, $mergeData);
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
            return $this;
        }

        // Call a php excel method
        elseif(method_exists($this->excel, $method))
        {
            // Call the method from the excel object with the given params
            return call_user_func_array(array($this->excel, $method), $params);
        }

        throw new LaravelExcelException('Laravel Excel method ['. $method .'] does not exist');
    }

}