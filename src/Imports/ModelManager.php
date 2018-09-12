<?php

namespace Maatwebsite\Excel\Imports;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelManager
{
    /**
     * @var Collection|Model[]
     */
    private $models;

    /**
     * @var DatabaseManager
     */
    private $db;

    /**
     * @param DatabaseManager $db
     */
    public function __construct(DatabaseManager $db)
    {
        $this->models = new Collection();
        $this->db     = $db;
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
     * @param Model $model
     */
    public function add(Model $model)
    {
        $this->models->push($model);
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

            unset($this->models);
            $this->models = new Collection();
        });
    }

    /**
     * Flush with a mass insert.
     */
    private function massFlush()
    {
        /** @var Model $model */
        $model = get_class($this->models[0]);

        $model::query()->insert(
            collect($this->models)->map->attributesToArray()->toArray()
        );
    }

    /**
     * Flush model per model.
     */
    private function singleFlush()
    {
        $this->models->each->saveOrFail();
    }
}