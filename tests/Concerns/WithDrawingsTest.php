<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

final class WithDrawingsTest extends TestCase
{
    public function test_can_export_with_a_single_drawing(): void
    {
        $export = new class implements Export, FromArray, WithDrawings
        {
            use Exportable;

            public function array(): array
            {
                return [['Patrick', 'Brouwers']];
            }

            public function drawings(): Drawing
            {
                $drawing = new Drawing;
                $drawing->setName('Logo');
                $drawing->setPath(__DIR__ . '/../Data/Disks/Local/icon.jpg');
                $drawing->setCoordinates('B2');

                return $drawing;
            }
        };

        $export->store('with-drawings.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-drawings.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        $this->assertCount(1, $sheet->getDrawingCollection());
        $this->assertSame('Logo', $sheet->getDrawingCollection()[0]->getName());
        $this->assertSame('B2', $sheet->getDrawingCollection()[0]->getCoordinates());
    }

    public function test_can_export_with_multiple_drawings(): void
    {
        $export = new class implements Export, FromArray, WithDrawings
        {
            use Exportable;

            public function array(): array
            {
                return [['Patrick', 'Brouwers']];
            }

            /**
             * @return Drawing[]
             */
            public function drawings(): array
            {
                $first = new Drawing;
                $first->setName('First');
                $first->setPath(__DIR__ . '/../Data/Disks/Local/icon.jpg');
                $first->setCoordinates('B2');

                $second = new Drawing;
                $second->setName('Second');
                $second->setPath(__DIR__ . '/../Data/Disks/Local/avatar.jpg');
                $second->setCoordinates('D2');

                return [$first, $second];
            }
        };

        $export->store('with-drawings-multiple.xlsx');

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-drawings-multiple.xlsx', 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        $this->assertCount(2, $sheet->getDrawingCollection());
        $this->assertSame('First', $sheet->getDrawingCollection()[0]->getName());
        $this->assertSame('Second', $sheet->getDrawingCollection()[1]->getName());
    }
}
