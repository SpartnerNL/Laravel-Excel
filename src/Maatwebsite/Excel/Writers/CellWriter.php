<?php namespace Maatwebsite\Excel\Writers;

use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;

/**
 *
 * LaravelExcel Excel writer
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class CellWriter {

    /**
     * Current $sheet
     * @var [type]
     */
    public $sheet;

    /**
     * Selected cells
     * @var [type]
     */
    public $cells;

    /**
     * Constructor
     * @param [type]                $cells [description]
     * @param LaravelExcelWorksheet $sheet [description]
     */
    public function __construct($cells, LaravelExcelWorksheet $sheet)
    {
        $this->cells = $cells;
        $this->sheet = $sheet;
    }

    /**
     * Set the background
     * @param [type] $color     [description]
     * @param string $type      [description]
     * @param string $colorType [description]
     */
    public function setBackground($color, $type = 'solid', $colorType = 'rgb')
    {
        $this->setStyle('fill', $color, $type, $colorType);
        return $this;
    }

    /**
     * Set the font color
     * @param [type] $color     [description]
     * @param string $colorType [description]
     */
    public function setFontColor($color, $colorType = 'rgb')
    {
        $this->setStyle('font', $color, false, $colorType);
        return $this;
    }

    /**
     * Set the style
     * @param [type]  $style     [description]
     * @param [type]  $color     [description]
     * @param boolean $type      [description]
     * @param string  $colorType [description]
     */
    protected function setStyle($styleType, $color, $type = false, $colorType = 'rgb')
    {
        // Get the cell style
        $style = $this->getCellStyle();

        // Set the styles
        $styles = is_array($color) ? $color : array(
            'type' => $type,
            'color' => array($colorType => str_replace('#', '', $color))
        );

        // Apply style from array
        $style->applyFromArray(array(
            $styleType => $styles
        ));
    }

    /**
     * Get the cell style
     * @return [type] [description]
     */
    protected function getCellStyle()
    {
        return $this->sheet->getStyle($this->cells);
    }

}