<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Events\BeforeWriting;
// @todo: Review naming consistency with the package
// @todo: Review Evtens\BeforeLoad.php
// @todo: Review Evtens\AfterLoad.php
// @todo: Review Reader.php
use Maatwebsite\Excel\Events\BeforeLoad;
use Maatwebsite\Excel\Events\AfterLoad;

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

        if (method_exists($this, 'beforeWriting')) {
            $listeners[BeforeWriting::class] = [static::class, 'beforeWriting'];
        }

        if (method_exists($this, 'beforeImport')) {
            $listeners[BeforeImport::class] = [static::class, 'beforeImport'];
        }

        if (method_exists($this, 'afterImport')) {
            $listeners[AfterImport::class] = [static::class, 'afterImport'];
        }

        if (method_exists($this, 'importFailed')) {
            $listeners[ImportFailed::class] = [static::class, 'importFailed'];
        }

        if (method_exists($this, 'beforeSheet')) {
            $listeners[BeforeSheet::class] = [static::class, 'beforeSheet'];
        }

        if (method_exists($this, 'afterSheet')) {
            $listeners[AfterSheet::class] = [static::class, 'afterSheet'];
        }

		// 21/10/2019 RRE
        if (method_exists($this, 'beforeLoad')) {
            $listeners[BeforeLoad::class] = [static::class, 'beforeLoad'];
        }

        if (method_exists($this, 'afterLoad')) {
            $listeners[AfterLoad::class] = [static::class, 'afterLoad'];
        }

        return $listeners;
    }
}
