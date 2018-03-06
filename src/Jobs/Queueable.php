<?php

namespace Maatwebsite\Excel\Jobs;

trait Queueable
{
    use \Illuminate\Bus\Queueable {
        chain as originalChain;
    }

    /**
     * @param $chain
     *
     * @return $this
     */
    public function chain($chain)
    {
        collect($chain)->each(function ($job) {
            $this->chained[] = serialize($job);
        });

        return $this;
    }
}