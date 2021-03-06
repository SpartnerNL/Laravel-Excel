<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait Styleable
{
    /**
     * @var array|null
     */
    protected $style;

    /**
     * @param array $style
     *
     * @return $this
     */
    public function style(array $style)
    {
        $this->style = array_merge_recursive($this->style, $style);

        return $this;
    }

    /**
     * @param bool $bold
     *
     * @return $this
     */
    public function bold(bool $bold = true)
    {
        $this->style['font']['bold'] = $bold;

        return $this;
    }

    /**
     * @param bool $italic
     *
     * @return $this
     */
    public function italic(bool $italic = true)
    {
        $this->style['font']['italic'] = $italic;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getStyle()
    {
        return $this->style;
    }

    public function writeStyles(Worksheet $worksheet)
    {
        if (!$this->getStyle()) {
            return;
        }

        $worksheet->getStyle($this->letter)->applyFromArray($this->getStyle());
    }
}
