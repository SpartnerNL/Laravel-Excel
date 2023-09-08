<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Events\AfterBatch;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\ImportFailed;
use ReflectionClass;

trait RegistersEventListeners
{
    /**
     * @return array
     */
    public function registerEvents(): array
    {
        $listenersClasses = [
            BeforeExport::class,
            BeforeWriting::class,
            BeforeImport::class,
            AfterImport::class,
            AfterBatch::class,
            AfterChunk::class,
            AfterChunk::class,
            ImportFailed::class,
            BeforeSheet::class,
            AfterSheet::class,
        ];
        $listeners = [];

        foreach ($listenersClasses as $class) {
            $name = (new ReflectionClass($class))->getShortName();
            // Method names are case insensitive in php
            if (method_exists($this, $name)) {
                // Allow methods to not be static
                $listeners[$class] = [$this, $name];
            }
        }

        return $listeners;
    }
}
