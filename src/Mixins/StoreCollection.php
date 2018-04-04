<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class StoreCollection
{
    /**
     * @return callable
     */
    public function storeExcel()
    {
        return function (string $filePath, string $disk = null, string $writerType = null, $withHeadings = false) {
            $export = new class($this, $withHeadings) implements FromCollection, WithHeadings {
                use Exportable;

                /**
                 * @var bool
                 */
                private $withHeadings;

                /**
                 * @var Collection
                 */
                private $collection;

                /**
                 * @param Collection $collection
                 * @param bool       $withHeadings
                 */
                public function __construct(Collection $collection, bool $withHeadings = false)
                {
                    $this->collection   = $collection->toBase();
                    $this->withHeadings = $withHeadings;
                }

                /**
                 * @return Collection
                 */
                public function collection()
                {
                    return $this->collection;
                }

                /**
                 * @return array
                 */
                public function headings(): array
                {
                    return $this->withHeadings ? $this->collection->collapse()->keys()->all() : [];
                }
            };

            return $export->store($filePath, $disk, $writerType);
        };
    }
}
