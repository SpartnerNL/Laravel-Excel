<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class SerializedQuery
{
    /**
     * @var string
     */
    public $query;

    /**
     * @var array
     */
    public $bindings;

    /**
     * @var string
     */
    public $connection;

    /**
     * @var string|null
     */
    public $model;

    /**
     * @var array
     */
    public $with = [];

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $builder
     */
    public function __construct($builder)
    {
        $this->query      = $builder->toSql();
        $this->bindings   = $builder->getBindings();
        $this->connection = $builder->getConnection()->getName();
        $this->with       = method_exists($builder, 'getEagerLoads') ? array_keys($builder->getEagerLoads()) : [];

        if ($builder instanceof EloquentBuilder) {
            $this->model = get_class($builder->getModel());
        }
    }

    /**
     * @return array
     */
    public function execute()
    {
        $connection = DB::connection($this->connection);

        return $this->hydrate(
            $connection->select($this->query, $this->bindings)
        );
    }

    /**
     * @param array $items
     *
     * @return array
     */
    public function hydrate(array $items)
    {
        if (!$instance = $this->newModelInstance()) {
            return $items;
        }

        $models = array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder($item);
        }, $items);

        if (!empty($this->with)) {
            $instance
                ->newQuery()
                ->with($this->with)
                ->eagerLoadRelations($models);
        }

        return $models;
    }

    /**
     * @return Model|null
     */
    private function newModelInstance()
    {
        if (null === $this->model) {
            return null;
        }

        /** @var Model $model */
        $model = new $this->model;

        $model->setConnection($this->connection);

        return $model;
    }
}
