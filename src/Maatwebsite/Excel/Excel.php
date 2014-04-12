<?php namespace Maatwebsite\Excel;

use \PHPExcel;
use Carbon\Carbon;
use \PHPExcel_Cell;
use \PHPExcel_IOFactory;
use \PHPExcel_Shared_Date;
use Illuminate\Support\Str;
use \PHPExcel_Style_NumberFormat;
use \PHPExcel_Worksheet_PageSetup;
use Illuminate\View\Environment as View;
use Maatwebsite\Excel\Readers\HTML_reader;
use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem as File;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;

/**
 * Laravel wrapper for PHPEXcel
 *
 * @version 0.3.0
 * @package maatwebsite/excel
 * @author Maatwebsite <info@maatwebsite.nl>
 * @contributors Maatwebsite, mewben, hicode, lollypopgr, floptwo, jonwhittlestone, BoHolm
 */

class Excel
{

    /**
     * Excel object
     * @var [type]
     */
    public $excel;

    /**
     * Html reader
     * @var [type]
     */
    protected $htmlReader;

    /**
     * Config repository
     * @var [type]
     */
    protected $config;

    /**
     * View factory
     * @var [type]
     */
    protected $viewFactory;

    /**
     * File system
     * @var [type]
     */
    protected $fileSystem;

    /**
     * Default CSV delimiter
     * @var string
     */
    protected $delimiter = ',';

    /**
     * Calculate formulas
     * @var boolean
     */
    protected $calculate = true;

    /**
     * Ignore empty cells
     * @var boolean
     */
    protected $ignoreEmpty = true;

    /**
     * Default date format
     * @var string
     */
    protected $dateFormat = 'Y-m-d';

    /**
     * Sheet heading to indices space replacer
     * @var string
     */
    protected $seperator = '_';


    /**
     * Construct Excel
     * @param PHPExcel    $excel      [description]
     * @param HTML_reader $htmlReader [description]
     * @param Config      $config     [description]
     * @param View        $view       [description]
     * @param File        $file       [description]
     */
    public function __construct(PHPExcel $excel, LaravelExcelWriter $writer, HTML_reader $htmlReader, Config $config, View $view, File $file)
    {
        // Set Excel dependencies
        $this->excel = $excel;
        $this->writer = $writer;
        $this->htmlReader = $htmlReader;

        // Set Laravel classes
        $this->config = $config;
        $this->viewFactory = $view;
        $this->fileSystem = $file;

        // Set defaults
        $this->_setDefaults();

        // Reset
        $this->reset();
    }

    /**
     * Create a new file
     * @param  [type] $title [description]
     * @return [type]        [description]
     */
    public function create($title)
    {

        // Set the default properties
        $this->excel->setDefaultProperties(array(
            'title' => $title
        ));

        // Inject our excel object
        $this->writer->injectExcel($this->excel);

        // Set the title
        $this->writer->setTitle($title);

        // Return the writer object
        return $this->writer;
    }

    /**
     * Set defaults
     */
    protected function _setDefaults()
    {
        // Set defaults
        $this->delimiter = $this->config->get('excel::delimiter', $this->delimiter);
        $this->calculate = $this->config->get('excel::calculate', $this->calculate);
        $this->ignoreEmpty = $this->config->get('excel::ignoreEmpty', $this->ignoreEmpty);
        $this->dateFormat = $this->config->get('excel::date_format', $this->dateFormat);
        $this->seperator = $this->config->get('excel::seperator', $this->seperator);
    }

    /**
     * Dynamically call methods
     * @param  [type] $method [description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function __call($method, $params)
    {

        // If the dynamic call starts with "with", add the var to the data array
        if(starts_with($method, 'with'))
        {
            $key = lcfirst(str_replace('with', '', $method));
            $this->addVars($key, reset($params));
        }

        // Call a php excel method
        elseif(method_exists($this->excel, $method))
        {
            // Call the method from the excel object with the given params
            return call_user_func_array(array($this->excel, $method), $params);
        }

        return $this;
    }

}