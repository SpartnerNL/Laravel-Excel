<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\StatementPrepared;
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
     * @param Builder $builder
     */
    public function __construct($builder)
    {
        $this->query      = $builder->toSql();
        $this->bindings   = $builder->getBindings();
        $this->connection = $builder->getConnection()->getName();

        if ($builder instanceof EloquentBuilder) {
            $this->model = get_class($builder->getModel());
        }
    }

    /**
     * @return array
     */
    public function execute()
    {
        /** @var Connection $connection */
        $connection = app('db')->connection($this->connection);

        Event::listen(StatementPrepared::class, function ($event) {
            $event->statement->setFetchMode(\PDO::FETCH_ASSOC);
        });

        return $this->hydrate(
            $connection->select($this->query, $this->bindings)
        );
    }

    /**
     * @param array $items
     *
     * @return mixed
     */
    public function hydrate(array $items)
    {
        if (!$instance = $this->newModelInstance()) {
            return $items;
        }

        return array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder($item);
        }, $items);
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
