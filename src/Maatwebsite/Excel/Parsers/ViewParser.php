<?php namespace Maatwebsite\Excel\Parsers;

use Maatwebsite\Excel\Readers\Html;
use Illuminate\Support\Facades\View;

/**
 *
 * LaravelExcel ViewParser
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class ViewParser {

    /**
     * View file
     * @var string
     */
    public $view;

    /**
     * Data array
     * @var array
     */
    public $data = [];

    /**
     * View merge data
     * @var array
     */
    public $mergeData = [];

    /**
     * Construct the view parser
     * @param Html $reader
     * @return \Maatwebsite\Excel\Parsers\ViewParser
     */
    public function __construct(Html $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Parse the view
     * @param  \Maatwebsite\Excel\Classes\LaravelExcelWorksheet $sheet
     * @return \Maatwebsite\Excel\Classes\LaravelExcelWorksheet
     */
    public function parse($sheet)
    {
        $html = View::make($this->getView(), $this->getData(), $this->getMergeData())->render();

        return $this->_loadHTML($sheet, $html);
    }

    /**
     * Load the HTML
     * @param  \Maatwebsite\Excel\Classes\LaravelExcelWorksheet $sheet
     * @param  string                                           $html
     * @return \Maatwebsite\Excel\Classes\LaravelExcelWorksheet
     */
    protected function _loadHTML($sheet, $html)
    {
        return $this->reader->load($html, true, $sheet);
    }

    /**
     * Get the view
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Get data
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get merge data
     * @return array
     */
    public function getMergeData()
    {
        return $this->mergeData;
    }

    /**
     * Set the view
     * @param bool|string $view
     */
    public function setView($view = false)
    {
        if ($view)
            $this->view = $view;
    }

    /**
     * Set the data
     * @param array $data
     */
    public function setData($data = [])
    {
        if (!empty($data))
            $this->data = array_merge($this->data, $data);
    }

    /**
     * Set the merge data
     * @param array $mergeData
     */
    public function setMergeData($mergeData = [])
    {
        if (!empty($mergeData))
            $this->mergeData = array_merge($this->mergeData, $mergeData);
    }
}