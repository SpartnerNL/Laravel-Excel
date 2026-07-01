<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;

trait ExtendedQueueable
{
    use Queueable {
        chain as originalChain;
    }

    /**
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
