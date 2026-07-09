<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StoreCollectionMixin
{
    public function storeExcel(): callable
    {
        return function (string $filePath, ?string $disk = null, ?string $writerType = null, $withHeadings = false): bool|PendingDispatch {
            $export = new class($this, $withHeadings) implements FromCollection, WithHeadings // @phpstan-ignore argument.type
            {
                use Exportable;

                /**
                 * @var Collection<array-key, mixed>
                 */
                private Collection $collection;

                /**
                 * @param  Collection<array-key, mixed>  $collection
                 */
                public function __construct(Collection $collection, private bool $withHeadings = false)
                {
                    $this->collection = $collection->toBase();
                }

                /**
                 * @return Collection<array-key, mixed>
                 */
                public function collection(): Collection
                {
                    return $this->collection;
                }

                /**
                 * @return array<int, mixed>
                 */
                public function headings(): array
                {
                    if (!$this->withHeadings) {
                        return [];
                    }

                    return is_array($first = $this->collection->first())
                        ? $this->collection->collapse()->keys()->all()
                        : array_keys($first->toArray());
                }
            };

            return $export->store($filePath, $disk, $writerType);
        };
    }
}
