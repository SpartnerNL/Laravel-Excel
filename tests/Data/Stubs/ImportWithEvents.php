<?php

namespace Seoperin\LaravelExcel\Tests\Data\Stubs;

use Seoperin\LaravelExcel\Events\AfterSheet;
use Seoperin\LaravelExcel\Events\AfterImport;
use Seoperin\LaravelExcel\Events\BeforeSheet;
use Seoperin\LaravelExcel\Concerns\Importable;
use Seoperin\LaravelExcel\Concerns\WithEvents;
use Seoperin\LaravelExcel\Events\BeforeImport;

class ImportWithEvents implements WithEvents
{
    use Importable;

    /**
     * @var callable
     */
    public $beforeImport;

    /**
     * @var callable
     */
    public $beforeSheet;

    /**
     * @var callable
     */
    public $afterSheet;

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeImport::class => $this->beforeImport ?? function () {
            },
            AfterImport::class => $this->afterImport ?? function () {
            },
            BeforeSheet::class => $this->beforeSheet ?? function () {
            },
            AfterSheet::class => $this->afterSheet ?? function () {
            },
        ];
    }
}
