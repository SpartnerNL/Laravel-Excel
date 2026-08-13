<?php

declare(strict_types=1);

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Contracts\QueuedSheetSourceHandler;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;

class HandlerRegistry
{
    /** @var array<SheetSourceHandler|QueuedSheetSourceHandler|string> */
    private array $handlers = [];

    public function register(SheetSourceHandler|QueuedSheetSourceHandler|string ...$handlers): void
    {
        foreach ($handlers as $handler) {
            array_unshift($this->handlers, $handler);
        }
    }

    public function findSyncHandler(Export $sheetExport): ?SheetSourceHandler
    {
        foreach ($this->handlers as $handler) {
            $resolved = is_string($handler) ? app($handler) : $handler;
            if ($resolved instanceof SheetSourceHandler && $resolved->canHandle($sheetExport)) {
                return $resolved;
            }
        }

        return null;
    }

    public function findQueuedHandler(Export $sheetExport): ?QueuedSheetSourceHandler
    {
        foreach ($this->handlers as $handler) {
            $resolved = is_string($handler) ? app($handler) : $handler;
            if ($resolved instanceof QueuedSheetSourceHandler && $resolved->canHandle($sheetExport)) {
                return $resolved;
            }
        }

        return null;
    }
}
