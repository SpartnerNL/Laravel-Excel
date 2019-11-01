<?php

namespace Maatwebsite\Excel\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Exceptions\RowSkippedException;
use Maatwebsite\Excel\Validators\RowValidator;
use Maatwebsite\Excel\Validators\ValidationException;
use Throwable;

class ModelManager
{
    /**
     * @var array
     */
    private $rows = [];

    /**
     * @var RowValidator
     */
    private $validator;

    /**
     * @param RowValidator $validator
     */
    public function __construct(RowValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param int   $row
     * @param array $attributes
     */
    public function add(int $row, array $attributes)
    {
        $this->rows[$row] = $attributes;
    }

    /**
     * @param ToModel $import
     * @param bool    $massInsert
     *
     * @throws ValidationException
     */
    public function flush(ToModel $import, bool $massInsert = false)
    {
        if ($import instanceof WithValidation) {
            $this->validateRows($import);
        }

        if ($massInsert) {
            $this->massFlush($import);
        } else {
            $this->singleFlush($import);
        }

        $this->rows = [];
    }

    /**
     * @param ToModel $import
     * @param array   $attributes
     *
     * @return Model[]|Collection
     */
    public function toModels(ToModel $import, array $attributes): Collection
    {
        $model = $import->model($attributes);

        if (null !== $model) {
            return \is_array($model) ? new Collection($model) : new Collection([$model]);
        }

        return new Collection([]);
    }

    /**
     * @param ToModel $import
     */
    private function massFlush(ToModel $import)
    {
        $this->rows()
             ->flatMap(function (array $attributes) use ($import) {
                 return $this->toModels($import, $attributes);
             })
             ->mapToGroups(function ($model) {
                 return [\get_class($model) => $this->prepare($model)->getAttributes()];
             })
             ->each(function (Collection $models, string $model) use ($import) {
                 try {
                     /* @var Model $model */
                     $model::query()->insert($models->toArray());
                 } catch (Throwable $e) {
                     if ($import instanceof SkipsOnError) {
                         $import->onError($e);
                     } else {
                         throw $e;
                     }
                 }
             });
    }

    /**
     * @param ToModel $import
     */
    private function singleFlush(ToModel $import)
    {
        $this
            ->rows()
            ->each(function (array $attributes) use ($import) {
                $this->toModels($import, $attributes)->each(function (Model $model) use ($import) {
                    try {
                        $model->saveOrFail();
                    } catch (Throwable $e) {
                        if ($import instanceof SkipsOnError) {
                            $import->onError($e);
                        } else {
                            throw $e;
                        }
                    }
                });
            });
    }

    /**
     * @param Model $model
     *
     * @return Model
     */
    private function prepare(Model $model): Model
    {
        if ($model->usesTimestamps()) {
            $time = $model->freshTimestamp();

            $updatedAtColumn = $model->getUpdatedAtColumn();

            // If model has updated at column and not manually provided.
            if ($updatedAtColumn && null === $model->{$updatedAtColumn}) {
                $model->setUpdatedAt($time);
            }

            $createdAtColumn = $model->getCreatedAtColumn();

            // If model has created at column and not manually provided.
            if ($createdAtColumn && null === $model->{$createdAtColumn}) {
                $model->setCreatedAt($time);
            }
        }

        return $model;
    }

    /**
     * @param WithValidation $import
     *
     * @throws ValidationException
     */
    private function validateRows(WithValidation $import)
    {
        try {
            $this->validator->validate($this->rows, $import);
        } catch (RowSkippedException $e) {
            foreach ($e->skippedRows() as $row) {
                unset($this->rows[$row]);
            }
        }
    }

    /**
     * @return Collection
     */
    private function rows(): Collection
    {
        return new Collection($this->rows);
    }
}
