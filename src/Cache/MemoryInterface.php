<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Cache;

use Psr\SimpleCache\CacheInterface;

interface MemoryInterface extends CacheInterface
{
    public function reachedMemoryLimit(): bool;

    public function flush(): array;
}
