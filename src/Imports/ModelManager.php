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
     * @var [type]
     */
    protected $massInsert;

    /**
     * @var [type]
     */
    protected $useTransaction;

    /**
     * @param DatabaseManager $db
     */
    public function __construct(
        DatabaseManager $db, 
        bool $massInsert = false,
        bool $useTransaction = true
    )
    {
        $this->db = $db;
        $this->massInsert = $massInsert;
        $this->useTransaction = $useTransaction;
    }

    /**
     * 
     * @return bool
     */
    public function getMassInsert()
    {
        return $this->massInsert;
    }

    /**
     * 
     * @return void
     */
    public function setMassInsert(bool $massInsert)
    {
        $this->massInsert = $massInsert;
    }

    /**
     * 
     * @return void
     */
    public function setUseTransaction(bool $useTransaction)
    {
        $this->useTransaction = $useTransaction;
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
     * @param bool | null $massInsert
     *
     * @throws \Throwable
     */
    public function flush($massInsert = null)
    {
        // 
        $massInsert = $massInsert ?? $this->getMassInsert();

        // save callback
        // 
        $callback = function () use ($massInsert) {

            $massInsert ? $this->massFlush() : $this->singleFlush();

            $this->models = [];

        };
        
        // in case we need to performe transaction
        // 
        if( $this->useTransaction )
        {
            return $this->transaction($callback);
        }

        return $callback();        
    }


    /**
     * Flush with a mass insert.
     */
    private function massFlush()
    {
        foreach ($this->models as $model => $models) 
        {
            try 
            {
                $model::query()->insert(
                    collect($models)->map->getAttributes()->toArray()
                );
            } catch (\Exception $e) {
                
            }
        }
    }

    /**
     * Flush model per model.
     */
    private function singleFlush()
    {
        try 
        {
            collect($this->models)->flatten()->each->saveOrFail();
        } 
        catch (\Exception $e) 
        {
            
        }
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
