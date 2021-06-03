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
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var bool
     */
    protected $collapsed = false;

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

    public function shouldAutoSize(): bool
    {
        return $this->autoSize;
    }

    /**
     * @return $this
     */
    public function hide()
    {
        $this->hidden = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function collapse()
    {
        $this->collapsed = true;

        return $this;
    }

    public function writeColumnDimensions(Worksheet $worksheet): void
    {
        $dimension = $worksheet->getColumnDimension($this->letter);

        if ($this->width) {
            $dimension->setWidth($this->width);
        }

        if ($this->hidden) {
            $dimension->setVisible(false);
        }

        if ($this->collapsed) {
            $dimension->setCollapsed(true);
        }

        $dimension->setAutoSize($this->shouldAutoSize());
    }
}
