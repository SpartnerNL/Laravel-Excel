<?php

namespace Maatwebsite\Excel\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\DatabaseManager;

class ModelManager
{
    /**
     * @var Model[][]
     */
    private $models = [];

    /**
     * @var DatabaseManager
     */
    private $db;

    /**
     * @param DatabaseManager $db
     */
    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    /**
     * @param callable $callback
     *
     * @return mixed
     */
    public function transaction(callable $callback)
    {
        return $this->db->transaction($callback);
    }

    /**
     * @param Model[] $models
     */
    public function add(Model ...$models)
    {
        foreach ($models as $model) {
            $name = get_class($model);

            if (!isset($this->models[$name])) {
                $this->models[$name] = [];
            }

            $this->models[$name][] = $this->prepare($model);
        }
    }

    /**
     * @param bool $massInsert
     *
     * @throws \Throwable
     */
    public function flush(bool $massInsert = false)
    {
        $this->transaction(function () use ($massInsert) {
            if ($massInsert) {
                $this->massFlush();
            } else {
                $this->singleFlush();
            }

            $this->models = [];
        });
    }

    /**
     * Flush with a mass insert.
     */
    private function massFlush()
    {
        foreach ($this->models as $model => $models) {
            $model::query()->insert(
                collect($models)->map->getAttributes()->toArray()
            );
        }
    }

    /**
     * Flush model per model.
     */
    private function singleFlush()
    {
        collect($this->models)->flatten()->each->saveOrFail();
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
