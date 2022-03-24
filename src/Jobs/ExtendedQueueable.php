<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;

trait ExtendedQueueable
{
    use Queueable {
        chain as originalChain;
    }

    /**
     * @param $chain
     * @return $this
     */
    public function chain($chain)
    {
        collect($chain)->each(function ($job) {
            $serialized      = method_exists($this, 'serializeJob') ? $this->serializeJob($job) : serialize($job);
            $this->chained[] = $serialized;
        });

        return $this;
    }
}
