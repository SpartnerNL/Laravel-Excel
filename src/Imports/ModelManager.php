<?php

namespace Maatwebsite\Excel\Imports;

use Throwable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\RowValidator;

class ModelManager
{
    /**
     * @var array
     */
    private $models = [];

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
        $this->models[$row] = $attributes;
    }

    /**
     * @param ToModel $import
     * @param bool    $massInsert
     */
    public function flush(ToModel $import, bool $massInsert = false)
    {
        if ($massInsert) {
            $this->massFlush($import);
        } else {
            $this->singleFlush($import);
        }

        $this->models = [];
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
        if ($import instanceof WithValidation) {
            $this->validator->validate($this->models, $import);
        }

        collect($this->models)
            ->map(function (array $attributes) use ($import) {
                return $this->toModels($import, $attributes);
            })
            ->flatten()
            ->mapToGroups(function (Model $model) {
                return [\get_class($model) => $this->prepare($model)->getAttributes()];
            })->each(function (Collection $models, string $model) use ($import) {
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
        collect($this->models)->each(function (array $attributes, $row) use ($import) {
            if ($import instanceof WithValidation) {
                $this->validator->validate([$row => $attributes], $import);
            }

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
}
