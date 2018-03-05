<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class StoreCollection
{
    /**
     * @return callable
     */
    public function storeExcel()
    {
        return function (string $filePath, string $disk = null, string $writerType = null) {
            $export = new class($this) implements FromCollection {
                use Exportable;

                /**
                 * @var Collection
                 */
                private $collection;

                /**
                 * @param Collection $collection
                 */
                public function __construct(Collection $collection)
                {
                    $this->collection = $collection->toBase();
                }

                /**
                 * @return Collection
                 */
                public function collection()
                {
                    return $this->collection;
                }
            };

            return $export->store($filePath, $disk, $writerType);
        };
    }
}
