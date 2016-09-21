<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Drivers\PHPExcel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Drivers\PHPExcel\Writers\LaravelExcelWriter;

interface Excel
{
    /**
     * Create a new file.
     * @param                     $filename
     * @param  callable|null      $callback
     * @return LaravelExcelWriter
     */
    public function create($filename, $callback = null);

    /**
     *  Load an existing file.
     *
     * @param  string             $file                 The file we want to load
     * @param  callback|null      $callback
     * @param  string|null        $encoding
     * @param  bool               $noBasePath
     * @param  null               $callbackConfigReader
     * @return LaravelExcelReader
     */
    public function load($file, $callback = null, $encoding = null, $noBasePath = false, $callbackConfigReader = null);

    /**
     * Set select sheets.
     * @param  $sheets
     * @return LaravelExcelReader
     */
    public function selectSheets($sheets = []);

    /**
     * Select sheets by index.
     * @param  array $sheets
     * @return $this
     */
    public function selectSheetsByIndex($sheets = []);

    /**
     * Batch import.
     * @param           $files
     * @param  callable $callback
     * @return PHPExcel
     */
    public function batch($files, callable $callback);

    /**
     * Create a new file and share a view.
     * @param  string             $view
     * @param  array              $data
     * @param  array              $mergeData
     * @return LaravelExcelWriter
     */
    public function shareView($view, $data = [], $mergeData = []);

    /**
     * Create a new file and load a view.
     * @param  string             $view
     * @param  array              $data
     * @param  array              $mergeData
     * @return LaravelExcelWriter
     */
    public function loadView($view, $data = [], $mergeData = []);

    /**
     * Set filters.
     * @param  array $filters
     * @return $this
     */
    public function registerFilters($filters = []);

    /**
     * Enable certain filters.
     * @param  string|array      $filter
     * @param  bool|false|string $class
     * @return $this
     */
    public function filter($filter, $class = false);

    /**
     * Get register, enabled (or both) filters.
     * @param  string|bool $key [description]
     * @return array
     */
    public function getFilters($key = false);
}
