<?php

namespace Maatwebsite\Excel\Mixins;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportAsMacro
{
    public function __invoke()
    {
        return function (string $filename, callable $mapping, ?string $disk = null, ?string $readerType = null) {
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
                 * @return Model|Model[]|null
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
