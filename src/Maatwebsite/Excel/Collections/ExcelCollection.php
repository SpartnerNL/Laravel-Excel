<?php namespace Maatwebsite\Excel\Collections;

use Illuminate\Support\Collection;

/**
 *
 * LaravelExcel ExcelCollection
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class ExcelCollection extends Collection {

    /**
     * Sheet title
     * @var [type]
     */
    protected $title;

    /**
     * Get the title
     * @return [type] [description]
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title
     * @param [type] $title [description]
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

}