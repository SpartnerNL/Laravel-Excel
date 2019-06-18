<?php

namespace Seoperin\LaravelExcel\Tests\Data\Stubs;

use Seoperin\LaravelExcel\Events\AfterSheet;
use Seoperin\LaravelExcel\Events\BeforeSheet;
use Seoperin\LaravelExcel\Concerns\Exportable;
use Seoperin\LaravelExcel\Concerns\WithEvents;
use Seoperin\LaravelExcel\Events\BeforeExport;
use Seoperin\LaravelExcel\Events\BeforeWriting;

class ExportWithEvents implements WithEvents
{
    use Exportable;

    /**
     * @var callable
     */
    public $beforeExport;

    /**
     * @var callable
     */
    public $beforeWriting;

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
            BeforeExport::class  => $this->beforeExport ?? function () {
            },
            BeforeWriting::class => $this->beforeWriting ?? function () {
            },
            BeforeSheet::class   => $this->beforeSheet ?? function () {
            },
            AfterSheet::class    => $this->afterSheet ?? function () {
            },
        ];
    }
}
