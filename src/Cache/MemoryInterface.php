<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Cache;

use Psr\SimpleCache\CacheInterface;

interface MemoryInterface extends CacheInterface
{
    public function reachedMemoryLimit(): bool;

    /**
     * @return array<string, mixed>
     */
    public function flush(): array;
}
