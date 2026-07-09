<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Bus\PendingBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Importer;

class ImportAsMacro
{
    public function __invoke(): callable
    {
        return function (string $filename, callable $mapping, ?string $disk = null, ?string $readerType = null): Importer|PendingDispatch|PendingBatch {
            /** @phpstan-ignore method.notFound */
            $import = new class($this->getModel()::class, $mapping) implements ToModel
            {
                use Importable;

                /**
                 * @var callable
                 */
                private $mapping;

                public function __construct(
                    private string $model,
                    callable $mapping,
                ) {
                    $this->mapping = $mapping;
                }

                /**
                 * @param  array<array-key, mixed>  $row
                 * @return Model|array<int, Model>|null
                 */
                public function model(array $row): Model|array|null
                {
                    return (new $this->model)->fill(
                        ($this->mapping)($row)
                    );
                }
            };

            return $import->import($filename, $disk, $readerType);
        };
    }
}
