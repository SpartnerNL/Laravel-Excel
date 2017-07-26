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
class CellWriter
{

    /**
     * Current $sheet
     * @var LaravelExcelWorksheet
     */
    public $sheet;

    /**
     * Selected cells
     * @var array
     */
    public $cells;

    /**
     * Constructor
     * @param array                 $cells
     * @param LaravelExcelWorksheet $sheet
     */
    public function __construct($cells, LaravelExcelWorksheet $sheet)
    {
        $this->cells = $cells;
        $this->sheet = $sheet;
    }

    /**
     * Set cell value
     * @param [type] $value
     * @return  CellWriter
     */
    public function setValue($value)
    {
        // Only set cell value for single cells
        if (!str_contains($this->cells, ':')) {
            $this->sheet->setCellValue($this->cells, $value);
        }

        return $this;
    }

    /**
     * Set cell url
     * @param [type] $url
     * @return  CellWriter
     */
    public function setUrl($url)
    {
        // Only set cell value for single cells
        if (!str_contains($this->cells, ':')) {
            $this->sheet->getCell($this->cells)->getHyperlink()->setUrl($url);
        }

        return $this;
    }

    /**
     * Set the background
     * @param string $color
     * @param string $type
     * @param string $colorType
     * @return  CellWriter
     */
    public function setBackground($color, $type = 'solid', $colorType = 'rgb')
    {
        return $this->setColorStyle('fill', $color, $type, $colorType);
    }

    /**
     * Set the font color
     * @param string $color
     * @param string $colorType
     * @return  CellWriter
     */
    public function setFontColor($color, $colorType = 'rgb')
    {
        return $this->setColorStyle('font', $color, false, $colorType);
    }

    /**
     * Set the font
     * @param $styles
     * @return  CellWriter
     */
    public function setFont($styles)
    {
        return $this->setStyle('font', $styles);
    }

    /**
     * Set font family
     * @param string $family
     * @return  CellWriter
     */
    public function setFontFamily($family)
    {
        return $this->setStyle('font', [
            'name' => $family,
        ]);
    }

    /**
     * Set font size
     * @param  string $size
     * @return  CellWriter
     */
    public function setFontSize($size)
    {
        return $this->setStyle('font', [
            'size' => $size,
        ]);
    }

    /**
     * Set font weight
     * @param  boolean|string $bold
     * @return  CellWriter
     */
    public function setFontWeight($bold = true)
    {
        return $this->setStyle('font', [
            'bold' => ($bold === 'bold' || $bold === true),
        ]);
    }

    /**
     * Set border
     * @param string      $top
     * @param bool|string $right
     * @param bool|string $bottom
     * @param bool|string $left
     * @return  CellWriter
     */
    public function setBorder($top = 'none', $right = 'none', $bottom = 'none', $left = 'none')
    {
        // Set the border styles
        $styles = is_array($top) ? $top : [
            'top'    => [
                'style' => $top,
            ],
            'left'   => [
                'style' => $left,
            ],
            'right'  => [
                'style' => $right,
            ],
            'bottom' => [
                'style' => $bottom,
            ],
        ];

        return $this->setStyle('borders', $styles);
    }

    /**
     * Set the text rotation
     * @param integer $alignment
     * @return  CellWriter
     */
    public function setTextRotation($degrees)
    {
        $style = $this->getCellStyle()->getAlignment()->setTextRotation($degrees);
        return $this;
    }

    /**
     * Set the alignment
     * @param string $alignment
     * @return  CellWriter
     */
    public function setAlignment($alignment)
    {
        return $this->setStyle('alignment', [
            'horizontal' => $alignment,
        ]);
    }

    /**
     * Set vertical alignment
     * @param string $alignment
     * @return  CellWriter
     */
    public function setValignment($alignment)
    {
        return $this->setStyle('alignment', [
            'vertical' => $alignment,
        ]);
    }

    /**
     * Set the text indent
     * @param integer $indent
     * @return  CellWriter
     */
    public function setTextIndent($indent)
    {
        $style = $this->getCellStyle()->getAlignment()->setIndent((int) $indent);
        return $this;
    }

    /**
     * Set the color style
     * @param         $styleType
     * @param string  $color
     * @param boolean $type
     * @param string  $colorType
     * @return  CellWriter
     */
    protected function setColorStyle($styleType, $color, $type = false, $colorType = 'rgb')
    {
        // Set the styles
        $styles = is_array($color) ? $color : [
            'type'  => $type,
            'color' => [$colorType => str_replace('#', '', $color)],
        ];

        return $this->setStyle($styleType, $styles);
    }

    /**
     * Set style
     * @param        $styleType
     * @param string $styles
     * @return  CellWriter
     */
    protected function setStyle($styleType, $styles)
    {
        // Get the cell style
        $style = $this->getCellStyle();

        // Apply style from array
        $style->applyFromArray([
            $styleType => $styles,
        ]);

        return $this;
    }

    /**
     * Get the cell style
     * @return \PHPExcel_Style
     */
    protected function getCellStyle()
    {
        return $this->sheet->getStyle($this->cells);
    }
}
