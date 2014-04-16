<?php namespace Maatwebsite\Excel\Writers;

use \Response;
use Carbon\Carbon;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;

class LaravelExcelWriter {

    /**
     * Spreadsheet title
     * @var [type]
     */
    public $title;

    /**
     * Excel object
     * @var [type]
     */
    public $excel;

    /**
     * Laravel response
     * @var [type]
     */
    protected $response;

    /**
     * Spreadsheet writer
     * @var [type]
     */
    public $writer;

    /**
     * Default extension
     * @var string
     */
    public $ext = 'xls';

    /**
     * Header Content-type
     * @var [type]
     */
    protected $contentType;

    /**
     * Spreadsheet is rendered
     * @var boolean
     */
    protected $rendered = false;

    /**
     * Sheet count
     * @var integer
     */
    protected $sheetCount = -1;

    /**
     * Construct new writer
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function injectExcel($excel)
    {
        $this->excel = $excel;
        $this->_reset();
    }

    /**
     * Set the spreadsheet title
     * @param [type] $title [description]
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Create a new sheet
     * @param  [type] $title    [description]
     * @param  [type] $callback [description]
     * @return [type]           [description]
     */
    public function sheet($title, $callback = false)
    {
        // Clone the active sheet
        $this->sheet = clone $this->excel->getActiveSheet();

        // Set the sheet title
        $this->sheet->setTitle($title);

        // Set the default page setup
        $this->sheet->setDefaultPageSetup();

        // Autosize columns
        $this->sheet->setAutosize(\Config::get('excel::sheets.autosize', false));

        // Do the ballback
        if($callback instanceof \Closure)
             call_user_func($callback, $this->sheet);

        $this->excel->addSheet($this->sheet->parsed());

        // Count sheets
        $this->sheetCount++;
        return $this;
    }

    /**
     * Add data
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public function with($array)
    {
        $this->fromArray($array);
        return $this;
    }

    /**
     * Export the spreadsheet
     * @return [type] [description]
     */
    public function export($ext = 'xls')
    {
        // Set the extension
        $this->ext = $ext;

        // Render the file
        $this->render();

        // Download the file
        $this->download();
    }

    /**
     * Download a file
     * @return [type] [description]
     */
    public function download()
    {
        // Set the headers
        $this->_setHeaders(array(

            'Content-Type'          => $this->contentType,
            'Content-Disposition'  => 'attachment; filename="' . $this->title . '.' . $this->ext . '"',
            'Cache-Control'         => 'max-age=0',
            'Cache-Control'         => 'max-age=1',
            'Expires'               => 'Mon, 26 Jul 1997 05:00:00 GMT', // Date in the past
            'Last-Modified'         =>  Carbon::now()->format('D, d M Y H:i:s'),
            'Cache-Control'         => 'cache, must-revalidate',
            'Pragma'                => 'public'

        ));

        // Check if writer isset
        if(!$this->writer)
            throw new LaravelExcelException('[ERROR] No writer was set.');

        // Download
        $this->writer->save('php://output');

        // End the script to prevent corrupted xlsx files
        exit;
    }

    /**
     * Start render of a new spreadsheet
     * @return [type] [description]
     */
    public function render()
    {
        // There should be enough sheets to continue rendering
        if($this->excel->getSheetCount() < 1)
            throw new LaravelExcelException('[ERROR] Aborting spreadsheet render: no sheets were created.');

       // dd($this->getView());

        // Set the format
        $this->_setFormat();

        // Set the writer
        $this->_setWriter();

        // File has been rendered
        $this->rendered = true;
    }

    /**
     * Set attributes
     * @param [type] $setter [description]
     * @param [type] $params [description]
     */
    protected function _setAttribute($setter, $params)
    {
        // Get the key
        $key = lcfirst(str_replace('set', '', $setter));

        // If is an allowed property
        if(in_array($key, $this->excel->allowedProperties))
        {
            // Set the properties
            call_user_func_array(array($this->excel->getProperties(), $setter), $params);
        }
    }

    /**
     * Set the write format
     */
    protected function _setFormat()
    {
        $this->ext  = strtolower($this->ext);

        switch($this->ext)
        {
            case 'xls':
                $this->format = 'Excel5';
                $this->contentType = 'application/vnd.ms-excel; charset=UTF-8';
                break;

            case 'xlsx':
                $this->format = 'Excel2007';
                $this->contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=UTF-8';
                break;

            case 'csv':
                $this->format = 'CSV';
                $this->contentType = 'application/csv; charset=UTF-8';
                break;

            default:
                $this->format = 'Excel5';
                $this->contentType = 'application/vnd.ms-excel; charset=UTF-8';
                break;
        }
    }

    /**
     * Set the writer
     */
    protected function _setWriter()
    {
        return $this->writer = \PHPExcel_IOFactory::createWriter($this->excel, $this->format);
    }

    /**
     * Set the headers
     */
    protected function _setHeaders($headers)
    {
        if ( headers_sent() ) throw new LaravelExcelException('[ERROR]: Headers already sent');

        foreach($headers as $header => $value)
        {
            header($header . ': ' . $value);
        }
    }

    /**
     * Reset the writer
     * @return [type] [description]
     */
    protected function _reset()
    {
        $this->excel->disconnectWorksheets();
    }

    /**
     * Dynamically call methods
     * @param  [type] $method [description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function __call($method, $params)
    {
        // Call a php excel method
        if(method_exists($this->excel, $method))
        {
            // Call the method from the excel object with the given params
            return call_user_func_array(array($this->excel, $method), $params);
        }

        // Call a php excel sheet method
        elseif(method_exists($this->excel->getSheet($this->sheetCount), $method))
        {
            // Call the method from the excel object with the given params
            return call_user_func_array(array($this->excel->getSheet($this->sheetCount), $method), $params);
        }

        // If the dynamic call starts with "with", add the var to the data array
        elseif(starts_with($method, 'set'))
        {
            $this->_setAttribute($method, $params);
        }


        return $this;
    }

    public function __destruct()
    {
        $this->excel->disconnectWorksheets();
        unset($this->excel);
    }

}