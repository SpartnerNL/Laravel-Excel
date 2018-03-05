<?php

namespace Maatwebsite\Excel\Macros;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class StoreCollection
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $writerType
     *
     * @return bool
     */
    public function __invoke(string $filePath, string $disk = null, string $writerType = null)
    {
        $export = new class($this->collection) implements FromCollection
        {
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
    }
}