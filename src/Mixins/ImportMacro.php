<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Bus\PendingBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Importer;

class ImportMacro
{
    public function __invoke(): callable
    {
        return function (string $filename, ?string $disk = null, ?string $readerType = null): Importer|PendingDispatch|PendingBatch {
            /** @phpstan-ignore method.notFound */
            $import = new class($this->getModel()::class) implements ToModel, WithHeadingRow
            {
                use Importable;

                public function __construct(
                    private string $model,
                ) {
                }

                /**
                 * @param  array<array-key, mixed>  $row
                 * @return Model|array<int, Model>|null
                 */
                public function model(array $row): Model|array|null
                {
                    return (new $this->model)->fill($row);
                }
            };

            return $import->import($filename, $disk, $readerType);
        };
    }
}
