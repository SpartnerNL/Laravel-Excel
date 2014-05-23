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
     * Set cell value
     * @param [type] $value [description]
     */
    public function setValue($value)
    {
        // Only set cell value for single cells
        if(!str_contains($this->cells, ':'))
        {
            $this->sheet->setCellValue($this->cells, $value);
        }

        return $this;
    }

    /**
     * Set the background
     * @param [type] $color     [description]
     * @param string $type      [description]
     * @param string $colorType [description]
     */
    public function setBackground($color, $type = 'solid', $colorType = 'rgb')
    {
        return $this->setColorStyle('fill', $color, $type, $colorType);
    }

    /**
     * Set the font color
     * @param [type] $color     [description]
     * @param string $colorType [description]
     */
    public function setFontColor($color, $colorType = 'rgb')
    {
        return $this->setColorStyle('font', $color, false, $colorType);
    }

    /**
     * Set the font
     * @param [type] $right [description]
     */
    public function setFont($styles)
    {
        return $this->setStyle('font', $styles);
    }

    /**
     * Set font family
     * @param [type] $family [description]
     */
    public function setFontFamily($family)
    {
        return $this->setStyle('font', array(
            'name'  => $family
        ));
    }

    /**
     * Set font size
     */
    public function setFontSize($size)
    {
        return $this->setStyle('font', array(
            'size'  => $size
        ));
    }

    /**
     * Set border
     * @param [type]  $top    [description]
     * @param boolean $right  [description]
     * @param boolean $bottom [description]
     * @param boolean $left   [description]
     */
    public function setBorder($top = 'none', $right = 'none', $bottom = 'none', $left = 'none')
    {
        // Set the border styles
        $styles = is_array($top) ? $top : array(
            'borders' => array(
                'top'   => array(
                    'style' => $top
                ),
                'left' => array(
                    'style' => $left,
                ),
                'right' => array(
                    'style' => $right,
                ),
                'bottom' => array(
                    'style' => $bottom,
                )
            )
        );

        return $this->setStyle('borders', $styles);
    }

    /**
     * Set the alignment
     * @param [type] $alignment [description]
     */
    public function setAlignment($alignment)
    {
        return $this->setStyle('alignment', array(
            'horizontal'    => $alignment
        ));
    }

    /**
     * Set vertical alignment
     * @param [type] $alignment [description]
     */
    public function setValignment($alignment)
    {
        return $this->setStyle('alignment', array(
            'vertical'    => $alignment
        ));
    }

    /**
     * Set the color style
     * @param [type]  $style     [description]
     * @param [type]  $color     [description]
     * @param boolean $type      [description]
     * @param string  $colorType [description]
     */
    protected function setColorStyle($styleType, $color, $type = false, $colorType = 'rgb')
    {
        // Set the styles
        $styles = is_array($color) ? $color : array(
            'type' => $type,
            'color' => array($colorType => str_replace('#', '', $color))
        );

        return $this->setStyle($styleType, $styles);
    }

    /**
     * Set style
     * @param [type] $styles [description]
     */
    protected function setStyle($styleType, $styles)
    {
        // Get the cell style
        $style = $this->getCellStyle();

        // Apply style from array
        $style->applyFromArray(array(
            $styleType => $styles
        ));

        return $this;
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