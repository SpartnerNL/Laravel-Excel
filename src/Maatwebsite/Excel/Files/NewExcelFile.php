<?php namespace Maatwebsite\Excel\Files;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel\Excel;

abstract class NewExcelFile extends File {

    /**
     * @var Application
     */
    protected $app;

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
     * @param Application $app
     * @param Excel       $excel
     */
    public function __construct(Application $app, Excel $excel)
    {
        $this->app = $app;
        $this->excel = $excel;
        $this->file = $this->createNewFile();
    }

    /**
     * Get file
     * @return string
     */
    abstract public function getFilename();

    /**
     * Load the file
     * @return \Maatwebsite\Excel\Readers\LaravelExcelReader
     */
    public function createNewFile()
    {
        // Load the file
        $file = $this->excel->create(
            $this->getFilename()
        );

        return $file;
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