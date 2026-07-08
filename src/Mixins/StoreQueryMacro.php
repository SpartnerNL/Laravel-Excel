<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Sheet;

class StoreQueryMacro
{
    public function __invoke()
    {
        return function (string $filePath, ?string $disk = null, ?string $writerType = null, $withHeadings = false): bool|PendingDispatch {
            $export = new class($this, $withHeadings) implements FromQuery, WithHeadings // @phpstan-ignore argument.type
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

            return $export->store($filePath, $disk, $writerType);
        };
    }
}
