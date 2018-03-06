<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Events\StatementPrepared;

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
     * @param Builder $builder
     */
    public function __construct($builder)
    {
        $this->query      = $builder->toSql();
        $this->bindings   = $builder->getBindings();
        $this->connection = $builder->getConnection()->getName();
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

        return $connection->select($this->query, $this->bindings);
    }
}
