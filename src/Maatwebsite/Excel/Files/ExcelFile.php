<?php namespace Maatwebsite\Excel\Files;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

abstract class ExcelFile extends File {

    /**
     * @param Application $app
     * @param Excel       $excel
     */
    public function __construct(Application $app, Excel $excel)
    {
        parent::__construct($app, $excel);
        $this->file = $this->loadFile();
    }

    /**
     * Get file
     * @return string
     */
    abstract public function getFile();

    /**
     * Get filters
     * @return array
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * Start importing
     */
    public function handleImport()
    {
        return $this->handle('Import');
    }

    /**
     * Load the file
     * @return \Maatwebsite\Excel\Readers\LaravelExcelReader
     */
    public function loadFile()
    {
        // Load filters
        $this->loadFilters();

        // Load the file
        $file = $this->excel->load(
            $this->getFile()
        );

        return $file;
    }

    /**
     * Load the filter
     * @return void
     */
    protected function loadFilters()
    {
        // Register the filters
        $this->excel->registerFilters(
            $this->getFilters()
        );

        // Loop through the filters
        foreach($this->getFilters() as $filter)
        {
            // Enable the filter
            $this->excel->filter($filter);
        }
    }

    /**
     * Dynamically call methods
     * @param  string $method
     * @param  array  $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        return call_user_func_array([$this->file, $method], $params);
    }
}