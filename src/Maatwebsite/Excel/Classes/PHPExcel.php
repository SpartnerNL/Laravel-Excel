<?php namespace Maatwebsite\Excel\Classes;

use \PHPExcel as PHPOffice_PHPExcel;
use Maatwebsite\Excel\Parsers\ViewParser;

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

    public $_workSheetCollection = array();

    /**
     * Allowed autofill properties
     * @var array
     */
    public $allowedProperties = array(
        'creator', 'lastModifiedBy', 'title', 'description', 'subject', 'keywords', 'category', 'manager', 'company'
    );

    /**
     * Create sheet and add it to this workbook
     *
     * @param  int|null $iSheetIndex Index where sheet should go (0,1,..., or null for last)
     * @return PHPExcel_Worksheet
     * @throws PHPExcel_Exception
     */
    public function createSheet($iSheetIndex = NULL, $title = false)
    {
        $newSheet = new LaravelExcelWorksheet($this, $title);
        $this->addSheet($newSheet, $iSheetIndex);
        return $newSheet;
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

            // set the property
            $properties->{$method}($value);
        }
    }

}