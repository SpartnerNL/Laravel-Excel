<?php namespace Maatwebsite\Excel;

use \Closure;
use \PHPExcel;
use Maatwebsite\Excel\Readers\Batch;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

/**
 *
 * Laravel wrapper for PHPExcel
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class Excel
{
    /**
     * Excel object
     * @var [type]
     */
    protected $excel;

    /**
     * Reader object
     * @var [type]
     */
    protected $reader;

    /**
     * Writer object
     * @var [type]
     */
    protected $writer;

    /**
     * Construct Excel
     * @param PHPExcel    $excel      [description]
     * @param HTML_reader $htmlReader [description]
     * @param Config      $config     [description]
     * @param View        $view       [description]
     * @param File        $file       [description]
     */
    public function __construct(PHPExcel $excel, LaravelExcelReader $reader, LaravelExcelWriter $writer)
    {
        // Set Excel dependencies
        $this->excel = $excel;
        $this->reader = $reader;
        $this->writer = $writer;
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

        // Disconnect worksheets to prevent unnecessary onces
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
     *  @param closure A callback
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
            call_user_func($callback, $this->reader;

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
        return $this->create($view)->shareView($view, $data, $mergeData);
    }

    /**
     * Create a new file and load a view
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
        if(method_exists($this->excel, $method))
        {
            // Call the method from the excel object with the given params
            return call_user_func_array(array($this->excel, $method), $params);
        }

        throw new LaravelExcelException('Laravel Excel method ['. $method .'] does not exist');
    }

}