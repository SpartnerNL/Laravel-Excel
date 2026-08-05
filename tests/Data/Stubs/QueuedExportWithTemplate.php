<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithExportTemplate;

/** @implements FromCollection<int, array{string, string}> */
class QueuedExportWithTemplate implements FromCollection, ShouldQueue, WithCustomChunkSize, WithCustomStartCell, WithExportTemplate
{
    use Exportable;

    public function __construct(
        private readonly string $templatePath,
    ) {
    }

    /**
     * @return Collection<int, array{string, string}>
     */
    public function collection(): Collection
    {
        return new Collection([
            ['Patrick', 'Brouwers'],
            ['Taylor', 'Otwell'],
        ]);
    }

    public function chunkSize(): int
    {
        return 1;
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function exportTemplate(): string
    {
        return $this->templatePath;
    }
}
