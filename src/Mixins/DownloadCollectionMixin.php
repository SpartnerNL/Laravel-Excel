<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Sheet;

class DownloadCollectionMixin
{
    public function downloadExcel(): callable
    {
        return function (string $fileName, ?string $writerType = null, $withHeadings = false, array $responseHeaders = []) {
            $export = new class($this, $withHeadings) implements FromCollection, WithHeadings // @phpstan-ignore argument.type
            {
                use Exportable;

                private Collection $collection;

                public function __construct(Collection $collection, private bool $withHeadings = false)
                {
                    $this->collection = $collection->toBase();
                }

                public function collection(): Collection
                {
                    return $this->collection;
                }

                public function headings(): array
                {
                    if (!$this->withHeadings) {
                        return [];
                    }

                    $firstRow = $this->collection->first();

                    if ($firstRow instanceof Arrayable || \is_object($firstRow)) {
                        return array_keys(Sheet::mapArraybleRow($firstRow));
                    }

                    return $this->collection->collapse()->keys()->all();
                }
            };

            return $export->download($fileName, $writerType, $responseHeaders);
        };
    }
}
