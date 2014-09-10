<?php namespace Maatwebsite\Excel\Files;

use Maatwebsite\Excel\Excel;

abstract class ExcelFile {

    /**
     * Excel instance
     * @var Excel
     */
    protected $excel;

    /**
     * Loaded file
     * @var \Maatwebsite\Excel\Readers\LaravelExcelReader
     */
    protected $file;

    /**
     * @param Excel $excel
     */
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
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
        $this->excel->registerFilters(
            $this->getFilters()
        );
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