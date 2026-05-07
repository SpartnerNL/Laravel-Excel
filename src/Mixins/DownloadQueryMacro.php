<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Sheet;

class DownloadQueryMacro
{
    public function __invoke()
    {
        return function (string $fileName, ?string $writerType = null, $withHeadings = false) {
            $export = new class($this, $withHeadings) implements FromQuery, WithHeadings
            {
                use Exportable;

                /**
                 * @param  $query
                 * @param  bool  $withHeadings
                 * @param  Builder  $query
                 */
                public function __construct(private $query, private bool $withHeadings = false)
                {
                }

                /**
                 * @return Builder
                 */
                public function query()
                {
                    return $this->query;
                }

                /**
                 * @return array
                 */
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
