<?php namespace Maatwebsite\Excel\Classes;

use PHPExcel as PHPOffice_PHPExcel;
use Illuminate\Support\Facades\Config;

/**
 *
 * Laravel wrapper for PHPExcel
 *
 * @category   Laravel Excel
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @copyright  Original Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class PHPExcel extends PHPOffice_PHPExcel {

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
     * @param  int|null   $iSheetIndex Index where sheet should go (0,1,..., or null for last)
     * @param bool|string $title
     * @throws \PHPExcel_Exception
     * @return LaravelExcelWorksheet
     */
    public function createSheet($iSheetIndex = null, $title = false)
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
     * @param  string $method
     * @return boolean
     */
    public function isChangeableProperty($method)
    {
        $name = lcfirst(str_replace('set', '', $method));

        return in_array($name, $this->getAllowedProperties()) ? true : false;
    }

    /**
     * Set default properties
     * @param array $custom
     * @return  void
     */
    public function setDefaultProperties($custom = array())
    {
        // Get the properties
        $properties = $this->getProperties();

        // Get fillable properties
        foreach ($this->getAllowedProperties() as $prop)
        {
            // Get the method
            $method = 'set' . ucfirst($prop);

            // get the value
            $value = in_array($prop, array_keys($custom)) ? $custom[$prop] : Config::get('excel::properties.' . $prop, null);

            // set the property
            call_user_func_array(array($properties, $method), array($value));
        }
    }

    /**
     * load info from parent obj
     * @param \PHPExcel $object
     * @return $this
     */
    function cloneParent(\PHPExcel $object)
    {
        // Init new reflection object
        $class = new \ReflectionClass(get_class($object));

        // Loop through all properties
        foreach($class->getProperties() as $property)
        {
            // Make the property public
            $property->setAccessible(true);

            // Set the found value to this sheet
            $property->setValue(
                $this,
                $property->getValue($object)
            );
        }

        return $this;
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
