<?php

namespace Maatwebsite\Excel\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\PersistRelations;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Exceptions\RowSkippedException;
use Maatwebsite\Excel\Imports\Persistence\CascadePersistManager;
use Maatwebsite\Excel\Validators\RowValidator;
use Maatwebsite\Excel\Validators\ValidationException;
use Throwable;

class ModelManager
{
    private array $rows = [];

    private bool $remembersRowNumber = false;

    public function __construct(
        private RowValidator $validator,
        private CascadePersistManager $cascade,
    ) {
    }

    public function add(int $row, array $attributes): void
    {
        $this->rows[$row] = $attributes;
    }

    public function setRemembersRowNumber(bool $remembersRowNumber): void
    {
        $this->remembersRowNumber = $remembersRowNumber;
    }

    /**
     * @throws ValidationException
     */
    public function flush(ToModel $import, bool $massInsert = false): void
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
     * @return Model[]|Collection
     */
    public function toModels(ToModel $import, array $attributes, ?int $rowNumber = null): Collection
    {
        if ($this->remembersRowNumber) {
            $import->rememberRowNumber($rowNumber);
        }

        return Collection::wrap($import->model($attributes));
    }

    private function massFlush(ToModel $import): void
    {
        $this->rows()
            ->flatMap(fn (array $attributes, $index) => $this->toModels($import, $attributes, $index))
            ->mapToGroups(fn ($model) => [$model::class => $this->prepare($model)->getAttributes()])
            ->each(function (Collection $models, string $model) use ($import): void {
                try {
                    /* @var Model $model */

                    if ($import instanceof WithUpserts) {
                        $model::query()->upsert(
                            $models->toArray(),
                            $import->uniqueBy(),
                            $import instanceof WithUpsertColumns ? $import->upsertColumns() : null
                        );

                        return;
                    } elseif ($import instanceof WithSkipDuplicates) {
                        $model::query()->insertOrIgnore($models->toArray());

                        return;
                    }

                    $model::query()->insert($models->toArray());
                } catch (Throwable $e) {
                    $this->handleException($import, $e);
                }
            });
    }

    private function singleFlush(ToModel $import): void
    {
        $this
            ->rows()
            ->each(function (array $attributes, $index) use ($import): void {
                $this->toModels($import, $attributes, $index)->each(function (Model $model) use ($import): void {
                    try {
                        if ($import instanceof WithUpserts) {
                            $model->upsert(
                                $model->getAttributes(),
                                $import->uniqueBy(),
                                $import instanceof WithUpsertColumns ? $import->upsertColumns() : null
                            );

                            return;
                        } elseif ($import instanceof WithSkipDuplicates) {
                            $model::query()->insertOrIgnore([$model->getAttributes()]);

                            return;
                        }

                        if ($import instanceof PersistRelations) {
                            $this->cascade->persist($model);
                        } else {
                            $model->saveOrFail();
                        }
                    } catch (Throwable $e) {
                        $this->handleException($import, $e);
                    }
                });
            });
    }

    private function prepare(Model $model): Model
    {
        if ($model->usesTimestamps()) {
            $time = $model->freshTimestamp();

            $updatedAtColumn = $model->getUpdatedAtColumn();

            // If model has updated at column and not manually provided.
            if ($updatedAtColumn && $model->{$updatedAtColumn} === null) {
                $model->setUpdatedAt($time);
            }

            $createdAtColumn = $model->getCreatedAtColumn();

            // If model has created at column and not manually provided.
            if ($createdAtColumn && $model->{$createdAtColumn} === null) {
                $model->setCreatedAt($time);
            }
        }

        return $model;
    }

    /**
     * @throws ValidationException
     */
    private function validateRows(WithValidation $import): void
    {
        try {
            $this->validator->validate($this->rows, $import);
        } catch (RowSkippedException $e) {
            foreach ($e->skippedRows() as $row) {
                unset($this->rows[$row]);
            }
        }
    }

    private function rows(): Collection
    {
        return new Collection($this->rows);
    }

    private function handleException(ToModel $import, Throwable $e): void
    {
        if (!$import instanceof SkipsOnError) {
            throw $e;
        }

        $import->onError($e);
    }
}
