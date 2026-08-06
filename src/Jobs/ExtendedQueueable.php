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
     */
    public function chain($chain): static
    {
        collect($chain)->each(function ($job): void {
            $serialized      = $this->serializeJob($job);
            $this->chained[] = $serialized;
        });

        return $this;
    }
}
