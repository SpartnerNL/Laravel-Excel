<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Sheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadQueryMacro
{
    public function __invoke()
    {
        return function (string $fileName, ?string $writerType = null, $withHeadings = false): Response|BinaryFileResponse {
            $export = new class($this, $withHeadings) implements FromQuery, WithHeadings
            {
                use Exportable;

                public function __construct(
                    private Builder $query,
                    private bool $withHeadings = false,
                ) {
                }

                public function query(): Builder
                {
                    return $this->query;
                }

                public function headings(): array
                {
                    if (!$this->withHeadings) {
                        return [];
                    }

                    $firstRow = (clone $this->query)->first();

                    if ($firstRow) {
                        return array_keys(Sheet::mapArraybleRow($firstRow));
                    }

                    return [];
                }
            };

            return $export->download($fileName, $writerType);
        };
    }
}
