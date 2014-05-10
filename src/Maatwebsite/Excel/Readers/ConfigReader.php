<?php namespace Maatwebsite\Excel\Readers;

use \Config;
use \Closure;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Collections\SheetCollection;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

/**
 *
 * LaravelExcel ConfigReader
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class ConfigReader {

    /**
     * Excel object
     * @var [type]
     */
    public $excel;

    /**
     * The sheet
     * @var [type]
     */
    public $sheet;

    /**
     * The sheetname
     * @var [type]
     */
    public $sheetName;

    /**
     * Collection of sheets (through the config reader)
     * @var [type]
     */
    public $sheetCollection;

    /**
     * Constructor
     * @param [type] $files [description]
     */
    public function __construct($excel, $config = 'excel::import', $callback = false)
    {
        // Set excel object
        $this->excel = $excel;

        // config name
        $this->configName = $config;

        // start
        $this->start($callback);
    }

    /**
     * Start the import
     * @param  boolean $callback [description]
     * @return [type]            [description]
     */
    public function start($callback = false)
    {
        // Init a new sheet collection
        $this->sheetCollection = new SheetCollection();

        // Get the sheet names
        if($sheets = $this->excel->getSheetNames())
        {
            // Loop through the sheets
            foreach($sheets as $index => $name)
            {
                // Set sheet name
                $this->sheetName = $name;

                // Set sheet
                $this->sheet = $this->excel->setActiveSheetIndex($index);

                // Do the callback
                if($callback instanceof Closure)
                {
                    call_user_func($callback, $this);
                }
                // If no callback, put it inside the sheet collection
                else
                {
                    $this->sheetCollection->push(clone $this);
                }
            }
        }
    }

    /**
     * Get the sheet collection
     * @return [type] [description]
     */
    public function getSheetCollection()
    {
        return $this->sheetCollection;
    }

    /**
     * Get value by index
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    protected function valueByIndex($field)
    {
        // Convert field name
        $field = snake_case($field);

        // Get coordinate
        if($coordinate = $this->getCoordinateByKey($field))
        {
            // return cell value by coordinate
            return $this->getCellValueByCoordinate($coordinate);
        }

        return null;
    }

    /**
     * Return cell value
     * @param  [type] $coordinate [description]
     * @return [type]             [description]
     */
    protected function getCellValueByCoordinate($coordinate)
    {
        if($this->sheet)
        {
            if(str_contains($coordinate, ':'))
            {
                // We want to get a range of cells
                $values = $this->sheet->rangeToArray($coordinate);
                return $values;
            }
            else
            {
                // We want 1 specific cell
                return $this->sheet->getCell($coordinate)->getValue();
            }
        }

        return null;
    }

    /**
     * Get the coordinates from the config file
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    protected function getCoordinateByKey($field)
    {
        return Config::get($this->configName . '.' . $this->sheetName . '.' . $field, false);
    }

    /**
     * Dynamically get a value by config
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public function __get($field)
    {
        return $this->valueByIndex($field);
    }
}