<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeWriting;

trait RegistersEventListeners
{

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        $listeners = [];

        if (method_exists($this, 'beforeExport')) {
            $listeners[BeforeExport::class] = [static::class, 'beforeExport'];
        }

        if (method_exists($this, 'beforeExport')) {
            $listeners[BeforeWriting::class] = [static::class, 'beforeWriting'];
        }

        if (method_exists($this, 'beforeExport')) {
            $listeners[BeforeSheet::class] = [static::class, 'beforeSheet'];
        }

        if (method_exists($this, 'beforeExport')) {
            $listeners[AfterSheet::class] = [static::class, 'afterSheet'];
        }

        return $listeners;
    }
}