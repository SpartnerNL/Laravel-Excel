<?php namespace Maatwebsite\Excel\Parsers;

use Maatwebsite\Excel\Readers\Html;

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
        $html = \View::make($this->view, $this->data, $this->mergeData)->render();
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
        return $this->reader->load($html, true, $sheet)->getActiveSheet();
    }

    /**
     * Set the view
     * @param [type] $view [description]
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Set the data
     * @param array $data [description]
     */
    public function setData($data = array())
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Set the merge data
     * @param array $mergeData [description]
     */
    public function setMergeData($mergeData = array())
    {
        $this->mergeData = array_merge($this->mergeData, $mergeData);
    }

}