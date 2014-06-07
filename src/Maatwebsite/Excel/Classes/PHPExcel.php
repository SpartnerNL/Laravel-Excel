<?php namespace Maatwebsite\Excel\Classes;

use Illuminate\Support\Facades\Config;
use PHPExcel as PHPOffice_PHPExcel;

/**
 *
 * Laravel wrapper for PHPExcel
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @copyright  Original Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class PHPExcel extends PHPOffice_PHPExcel
{
    /**
     * Allowed autofill properties
     * @var array
     */
    public $allowedProperties = array(
        'creator',
        'lastModifiedBy',
        'description',
        'subject',
        'keywords',
        'category',
        'manager',
        'company'
    );

    /**
     * Create sheet and add it to this workbook
     *
     * @param  int|null $iSheetIndex Index where sheet should go (0,1,..., or null for last)
     * @param string $title
     * @return LaravelExcelWorksheet
     * @throws PHPExcel_Exception
     */
    public function createSheet($iSheetIndex = NULL, $title = false)
    {
        // Init new Laravel Excel worksheet
        $newSheet = new LaravelExcelWorksheet($this, $title);

        // Add the sheet
        $this->addSheet($newSheet, $iSheetIndex);

        // Return the sheet
        return $newSheet;
    }

    /**
     * Check if the user change change the workbook property
     * @param  string  $method
     * @return boolean
     */
    public function isChangeableProperty($method)
    {
        $name = lcfirst(str_replace('set', '', $method));
        return in_array($name, $this->getAllowedProperties()) ? true : false;

    }

    /**
     * Set default properties
     * @param string $custom
     * @return  void
     */
    public function setDefaultProperties($custom)
    {
        // Get the properties
        $properties = $this->getProperties();

        // Get fillable properties
        foreach($this->getAllowedProperties() as $prop)
        {
            // Get the method
            $method = 'set' . ucfirst($prop);

            // get the value
            $value = in_array($prop, array_keys($custom)) ? $custom[$prop] : Config::get('excel::properties.' . $prop, NULL);

            // set the property
            call_user_func_array(array($properties, $method), array($value));
        }
    }

    /**
     * Return all allowed properties
     * @return array
     */
    public function getAllowedProperties()
    {
        return $this->allowedProperties;
    }

}