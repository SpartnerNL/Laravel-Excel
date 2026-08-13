<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait Sizeable
{
    protected ?float $width = null;

    protected bool $autoSize = false;

    protected bool $hidden = false;

    protected bool $collapsed = false;

    public function width(float $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function autoSize(): static
    {
        $this->autoSize = true;

        return $this;
    }

    public function shouldAutoSize(): bool
    {
        return $this->autoSize;
    }

    public function hide(): static
    {
        $this->hidden = true;

        return $this;
    }

    public function collapse(): static
    {
        $this->collapsed = true;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function writeColumnDimensions(Worksheet $worksheet): void
    {
        $dimension = $worksheet->getColumnDimension($this->letter);

        if ($this->width !== null) {
            $dimension->setWidth($this->width);
        }

        if ($this->hidden) {
            $dimension->setVisible(false);
        }

        if ($this->collapsed) {
            $dimension->setCollapsed(true);
        }

        // Only ever opt in. Asserting false would override a sheet-wide
        // ShouldAutoSize for every column that simply didn't ask.
        if ($this->autoSize) {
            $dimension->setAutoSize(true);
        }
    }
}
