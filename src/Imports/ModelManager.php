<?php

namespace Maatwebsite\Excel\Imports;

use Illuminate\Support\Collection;
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

            $this->models[$name][] = $model;
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
}
