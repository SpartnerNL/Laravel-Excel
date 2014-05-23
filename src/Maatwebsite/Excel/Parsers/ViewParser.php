<?php namespace Maatwebsite\Excel\Parsers;

use \View;
use Maatwebsite\Excel\Readers\Html;

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
     * @var [type]
     */
    public $view;

    /**
     * Data array
     * @var array
     */
    public $data = array();

    /**
     * View merge data
     * @var array
     */
    public $mergeData = array();

    /**
     * Construct the view parser
     * @param HTML_Reader $reader [description]
     */
    public function __construct(Html $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Parse the view
     * @param  [type] $sheet [description]
     * @return [type]        [description]
     */
    public function parse($sheet)
    {
        $html = View::make($this->getView(), $this->getData(), $this->getMergeData())->render();
        return $this->_loadHTML($sheet, $html);
    }

    /**
     * Load the HTML
     * @param  [type] $sheet [description]
     * @param  [type] $html  [description]
     * @return [type]        [description]
     */
    protected function _loadHTML($sheet, $html)
    {
        return $this->reader->load($html, true, $sheet);
    }

    /**
     * Get the view
     * @return [type] [description]
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Get data
     * @return [type] [description]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get merge data
     * @return [type] [description]
     */
    public function getMergeData()
    {
        return $this->mergeData;
    }

    /**
     * Set the view
     * @param [type] $view [description]
     */
    public function setView($view = false)
    {
        if($view)
            $this->view = $view;
    }

    /**
     * Set the data
     * @param array $data [description]
     */
    public function setData($data = array())
    {
        if(!empty($data))
            $this->data = array_merge($this->data, $data);
    }

    /**
     * Set the merge data
     * @param array $mergeData [description]
     */
    public function setMergeData($mergeData = array())
    {
        if(!empty($mergeData))
            $this->mergeData = array_merge($this->mergeData, $mergeData);
    }

}