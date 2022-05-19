<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Testing\Fluent\Concerns\Has;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Events\BeforeChunk;
use Maatwebsite\Excel\HasEventBus;
use Maatwebsite\Excel\Jobs\AppendQueryToSheet;

trait WithChunkEvents
{
    use HasEventBus;

    /**
     * @param callable $callable
     * @param          $exportable
     *
     * @return \Closure
     */
    protected function withEventHandling(callable $callable, $exportable) {
        if(!$exportable instanceof WithEvents) {
            return $callable;
        }

        return function() use ($callable, $exportable) {
            $this->prepareEventBus($exportable)
                ->beforeChunk($exportable);

            $callable();

            $this->afterChunk($exportable)
                ->garbageCollect();
        };
    }
    /**
     * @return $this
     */
    protected function prepareEventBus($exportable) {
        if($exportable instanceof WithEvents){
            $this->registerListeners($exportable->registerEvents());
        }

        return $this;
    }

    /**
     * @param $exportable
     *
     * @return $this
     */
    protected function beforeChunk($exportable) {
        $this->raise(new BeforeChunk($exportable));

        return $this;
    }

    /**
     * @param $exportable
     *
     * @return $this
     */
    protected function afterChunk($exportable) {
        $this->raise(new AfterChunk($exportable));

        return $this;
    }

    /**
     * @return $this
     */
    protected function garbageCollect()
    {
        $this->clearListeners();

        return $this;
    }
}