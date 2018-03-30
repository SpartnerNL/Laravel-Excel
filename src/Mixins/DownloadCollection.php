<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DownloadCollection
{
    /**
     * @return callable
     */
    public function downloadExcel()
    {
        return function (string $fileName, string $writerType = null, $withHeadings = false) {
            $export = new class($this) implements FromCollection, WithHeadings {
                use Exportable;

                /**
                 * @var bool
                 */
                public $withHeadings = false;

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

                /**
                 * @return array
                 */
                public function headings(): array
                {
                    return $this->withHeadings ? $this->collection->collapse()->keys()->all() : [];
                }
            };

            $export->withHeadings = $withHeadings;

            return $export->download($fileName, $writerType);
        };
    }
}
