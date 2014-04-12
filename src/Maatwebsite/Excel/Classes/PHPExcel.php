<?php namespace Maatwebsite\Excel\Classes;

use \PHPExcel as PHPOffice_PHPExcel;

/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    ##VERSION##, ##DATE##
 */


/**
 * PHPExcel
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel extends PHPOffice_PHPExcel
{

    /**
     * Active sheet index
     *
     * @var int
     */
    private $_activeSheetIndex = 0;

    /**
     * View file
     * @var [type]
     */
    public $view;

    /**
     * Data
     * @var [type]
     */
    public $data;

    /**
     * Merge data
     * @var [type]
     */
    public $mergeData;

    /**
     * Allowed autofill properties
     * @var array
     */
    public $allowedProperties = array(
        'creator', 'lastModifiedBy', 'title', 'description', 'subject', 'keywords', 'category', 'manager', 'company'
    );

     /**
     * Create a new PHPExcel with one Worksheet
     */
    public function __construct()
    {
        parent::__construct();

        // Lets inject our custom Worksheet

        $this->_workSheetCollection = array();
        $this->_workSheetCollection[] = new LaravelExcelWorksheet($this);

    }

    /**
     * Set default properties
     */
    public function setDefaultProperties($custom)
    {
        $properties = $this->getProperties();

        // Get fillable properties
        foreach($this->allowedProperties as $prop)
        {
            // Get the method
            $method = 'set' . ucfirst($prop);

            // get the value
            $value = in_array($prop, array_keys($custom)) ? $custom[$prop] : \Config::get('excel::properties.' . $prop, NULL);

            // set the propertie
            $properties->{$method}($value);
        }
    }

    /**
     * Get active sheet
     *
     * @return PHPExcel_Worksheet
     */
    public function getActiveSheet()
    {
        return $this->_workSheetCollection[$this->_activeSheetIndex];
    }

}