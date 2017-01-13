<?php

namespace Maatwebsite\Excel;

interface Writer
{
    /**
     * Export the spreadsheet.
     * @param  string                $ext
     * @param  array                 $headers
     * @throws LaravelExcelException
     */
    public function export($ext = 'xls', array $headers = []);

    /**
     * Convert and existing file to newly requested extension.
     * @param       $ext
     * @param array $headers
     */
    public function convert($ext, array $headers = []);

    /**
     * Export and download the spreadsheet.
     * @param string $ext
     * @param array  $headers
     */
    public function download($ext = 'xls', array $headers = []);

    /**
     * Return the spreadsheet file as a string.
     * @param  string                $ext
     * @throws LaravelExcelException
     * @return string
     */
    public function string($ext = 'xls');

    /**
     * Store the excel file to the server.
     * @param  string             $ext
     * @param  bool               $path
     * @param  bool               $returnInfo
     * @return LaravelExcelWriter
     */
    public function store($ext = 'xls', $path = false, $returnInfo = false);

    /**
     *  Store the excel file to the server.
     * @param  str|string         $ext        The file extension
     * @param  bool|str           $path       The save path
     * @param  bool               $returnInfo
     * @return LaravelExcelWriter
     */
    public function save($ext = 'xls', $path = false, $returnInfo = false);
}
