<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Contracts\Queue\ShouldQueue;

interface ShouldQueueWithoutChain extends ShouldQueue
{
}
