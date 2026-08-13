<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\ImageContent;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Image extends Column
{
    protected ?float $height = null;

    protected ?string $disk = null;

    public function height(float $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Drawings are only loaded when the reader is not in read-only mode.
     */
    public function needsStyleInformation(): bool
    {
        return true;
    }

    protected function value(Cell $cell): ?ImageContent
    {
        foreach ($cell->getWorksheet()->getDrawingCollection() as $drawing) {
            if ($drawing->getCoordinates() === $cell->getCoordinate()) {
                return ImageContent::from($drawing);
            }
        }

        return null;
    }

    /**
     * @throws Exception
     */
    protected function writeValue(Worksheet $worksheet, Cell $cell, mixed $value): void
    {
        $path = is_string($value) ? realpath($value) : false;

        if ($path === false) {
            return;
        }

        $drawing = new Drawing;
        $drawing->setCoordinates($cell->getCoordinate());
        $drawing->setPath($path);
        $drawing->setWorksheet($worksheet);

        if ($this->height !== null) {
            $drawing->setHeight((int) $this->height);
            $worksheet->getRowDimension($cell->getRow())->setRowHeight($this->height);
        }

        if ($this->width !== null) {
            $drawing->setWidth((int) $this->width);
        }
    }

    protected function toExcelValue(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        return Storage::disk($this->disk)->path($value);
    }
}
