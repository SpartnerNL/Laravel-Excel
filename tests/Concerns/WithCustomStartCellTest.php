<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Tests\TestCase;

final class WithCustomStartCellTest extends TestCase
{
    protected readonly Excel $SUT;

    protected function setUp(): void
    {
        parent::setUp();

        $this->SUT = $this->app->make(Excel::class);
    }

    public function test_can_store_collection_with_custom_start_cell(): void
    {
        $export = new class implements FromCollection, WithCustomStartCell
        {
            /**
             * @return Collection<int, mixed>
             */
            public function collection(): Collection
            {
                return new Collection([
                    ['A1', 'B1'],
                    ['A2', 'B2'],
                ]);
            }

            public function startCell(): string
            {
                return 'B2';
            }
        };

        $this->SUT->store($export, 'custom-start-cell.csv');

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/custom-start-cell.csv', 'Csv');

        $this->assertSame([
            [null, null, null],
            [null, 'A1', 'B1'],
            [null, 'A2', 'B2'],
        ], $contents);
    }

    public function test_can_append_multiple_chunks_with_custom_start_cell_without_export_template(): void
    {
        $export = new class implements FromCollection, WithCustomStartCell
        {
            /**
             * @return Collection<int, array{int}>
             */
            public function collection(): Collection
            {
                return new Collection(array_map(
                    fn (int $row): array => [$row],
                    range(1, 1001)
                ));
            }

            public function startCell(): string
            {
                return 'B2';
            }
        };

        $this->SUT->store($export, 'custom-start-cell-without-export-template.xlsx');

        $spreadsheet = $this->read(
            __DIR__ . '/../Data/Disks/Local/custom-start-cell-without-export-template.xlsx',
            'Xlsx'
        );
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame(1, $sheet->getCell('B2')->getValue());
        $this->assertSame(1000, $sheet->getCell('B1001')->getValue());
        $this->assertSame(1001, $sheet->getCell('B1002')->getValue());
    }
}
