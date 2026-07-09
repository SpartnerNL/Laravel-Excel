<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;

trait ExtendedQueueable
{
    use Queueable {
        chain as originalChain;
    }

    /**
     * @param  array<int, object>  $chain
     * @return $this
     */
    public function chain($chain)
    {
        collect($chain)->each(function ($job): void {
            $serialized      = $this->serializeJob($job);
            $this->chained[] = $serialized;
        });

        return $this;
    }
}
