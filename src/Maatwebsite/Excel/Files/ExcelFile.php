<?php namespace Maatwebsite\Excel\Files;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

abstract class ExcelFile extends File {

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
     * @var Application
     */
    private $app;

    /**
     * @param Application $app
     * @param Excel       $excel
     */
    public function __construct(Application $app, Excel $excel)
    {
        $this->app = $app;
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
     * Start importing
     */
    public function handleImport()
    {
        // Get the handler
        $handler = $this->getHandler();

        // Call the handle method and inject the file
        return $handler->handle($this);
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
     * Get handler
     * @return mixed
     */
    protected function getHandler()
    {
        return $this->app->make(
            $this->getHandlerClassName('Import')
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