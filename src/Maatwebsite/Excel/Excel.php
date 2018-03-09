<?php namespace Maatwebsite\Excel;

use Closure;
use Maatwebsite\Excel\Readers\Batch;
use Maatwebsite\Excel\Classes\PHPExcel;
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
class Excel {

    /**
     * Filter
     * @var array
     */
    protected $filters = [
        'registered' =>  [],
        'enabled'    =>  []
    ];

    /**
     * Excel object
     * @var PHPExcel
     */
    protected $excel;

    /**
     * Reader object
     * @var LaravelExcelReader
     */
    protected $reader;

    /**
     * Writer object
     * @var LaravelExcelWriter
     */
    protected $writer;

    /**
     * Construct Excel
     * @param  PHPExcel           $excel
     * @param  LaravelExcelReader $reader
     * @param  LaravelExcelWriter $writer
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
     * @param                $filename
     * @param  callable|null $callback
     * @return LaravelExcelWriter
     */
    public function create($filename, $callback = null)
    {
        // Writer instance
        $writer = clone $this->writer;

        // Disconnect worksheets to prevent unnecessary ones
        $this->excel->disconnectWorksheets();

        // Inject our excel object
        $writer->injectExcel($this->excel);

        // Set the filename and title
        $writer->setFileName($filename);
        $writer->setTitle($filename);

        // Do the callback
         if (is_callable($callback))
            call_user_func($callback, $writer);

        // Return the writer object
        return $writer;
    }

    /**
     *
     *  Load an existing file
     *
     * @param  string        $file The file we want to load
     * @param  callback|null $callback
     * @param  string|null   $encoding
     * @param  bool          $noBasePath
     * @param  callback|null $callbackConfigReader
     * @return LaravelExcelReader
     */
    public function load($file, $callback = null, $encoding = null, $noBasePath = false, $callbackConfigReader = null)
    {
        // Reader instance
        $reader = clone $this->reader;

        // Inject excel object
        $reader->injectExcel($this->excel);

        // Enable filters
        $reader->setFilters($this->filters);

        // Set the encoding
        $encoding = is_string($callback) ? $callback : $encoding;

        // Start loading
        $reader->load($file, $encoding, $noBasePath, $callbackConfigReader);

        // Do the callback
        if ($callback instanceof Closure)
            call_user_func($callback, $reader);

        // Return the reader object
        return $reader;
    }

    /**
     * Set select sheets
     * @param  $sheets
     * @return LaravelExcelReader
     */
    public function selectSheets($sheets = [])
    {
        $sheets = is_array($sheets) ? $sheets : func_get_args();
        $this->reader->setSelectedSheets($sheets);

        return $this;
    }

    /**
     * Select sheets by index
     * @param array $sheets
     * @return $this
     */
    public function selectSheetsByIndex($sheets = [])
    {
        $sheets = is_array($sheets) ? $sheets : func_get_args();
        $this->reader->setSelectedSheetIndices($sheets);

        return $this;
    }

    /**
     * Batch import
     * @param           $files
     * @param  callback $callback
     * @return PHPExcel
     */
    public function batch($files, Closure $callback)
    {
        $batch = new Batch;

        return $batch->start($this, $files, $callback);
    }

    /**
     * Create a new file and share a view
     * @param  string $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return LaravelExcelWriter
     */
    public function shareView($view, $data = [], $mergeData = [])
    {
        return $this->create($view)->shareView($view, $data, $mergeData);
    }

    /**
     * Create a new file and load a view
     * @param  string $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return LaravelExcelWriter
     */
    public function loadView($view, $data = [], $mergeData = [])
    {
        return $this->shareView($view, $data, $mergeData);
    }

    /**
     * Set filters
     * @param   array $filters
     * @return  Excel
     */
    public function registerFilters($filters = [])
    {
        // If enabled array key exists
        if(array_key_exists('enabled', $filters))
        {
            // Set registered array
            $registered = $filters['registered'];

            // Filter on enabled
            $this->filter($filters['enabled']);
        }
        else
        {
            $registered = $filters;
        }

        // Register the filters
        $this->filters['registered'] = !empty($this->filters['registered']) ? array_merge($this->filters['registered'], $registered) : $registered;
        return $this;
    }

    /**
     * Enable certain filters
     * @param  string|array     $filter
     * @param bool|false|string $class
     * @return Excel
     */
    public function filter($filter, $class = false)
    {
        // Add multiple filters
        if(is_array($filter))
        {
            $this->filters['enabled'] = !empty($this->filters['enabled']) ? array_merge($this->filters['enabled'], $filter) : $filter;
        }
        else
        {
            // Add single filter
            $this->filters['enabled'][] = $filter;

            // Overrule filter class for this request
            if($class)
                $this->filters['registered'][$filter] = $class;
        }

        // Remove duplicates
        $this->filters['enabled'] = array_unique($this->filters['enabled']);

        return $this;
    }

    /**
     * Get register, enabled (or both) filters
     * @param  string|boolean $key [description]
     * @return array
     */
    public function getFilters($key = false)
    {
        return $key ? $this->filters[$key] : $this->filters;
    }

    /**
     * Dynamically call methods
     * @throws LaravelExcelException
     */
    public function __call($method, $params)
    {
        // If the dynamic call starts with "with", add the var to the data array
        if (method_exists($this->excel, $method))
        {
            // Call the method from the excel object with the given params
            return call_user_func_array([$this->excel, $method], $params);
        }

        // If reader method exists, call that one
        if (method_exists($this->reader, $method))
        {
            // Call the method from the reader object with the given params
            return call_user_func_array([$this->reader, $method], $params);
        }

        throw new LaravelExcelException('Laravel Excel method [' . $method . '] does not exist');
    }
}
