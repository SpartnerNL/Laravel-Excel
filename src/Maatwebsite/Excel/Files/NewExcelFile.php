<?php namespace Maatwebsite\Excel\Files;

use Illuminate\Foundation\Application;
use Maatwebsite\Excel\Excel;

abstract class NewExcelFile extends File {

    /**
     * @param Application $app
     * @param Excel       $excel
     */
    public function __construct(Application $app, Excel $excel)
    {
        parent::__construct($app, $excel);
        $this->file = $this->createNewFile();
    }

    /**
     * Get file
     * @return string
     */
    abstract public function getFilename();

    /**
     * Start importing
     */
    public function handleExport()
    {
        return $this->handle( 
            get_class($this) 
        );
    }


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