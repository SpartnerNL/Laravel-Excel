<?php

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait Sizeable
{
    /**
     * @var int|null
     */
    protected $width;

    /**
     * @var bool
     */
    protected $autoSize = false;

    /**
     * @param int|null $width
     *
     * @return $this
     */
    public function width(int $width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return $this
     */
    public function autoSize()
    {
        $this->autoSize = true;

        return $this;
    }

    public function writeSize(Worksheet $worksheet)
    {
        $dimension = $worksheet->getColumnDimension($this->letter);

        if ($this->width) {
            $dimension->setWidth($this->width);
        }

        $dimension->setAutoSize($this->shouldAutoSize());
    }
}
