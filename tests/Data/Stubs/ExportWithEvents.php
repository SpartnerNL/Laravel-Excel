<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeWriting;

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

    public function registerEvents(): array
    {
        return [
            BeforeExport::class => $this->beforeExport ?? function (): void {
            },
            BeforeWriting::class => $this->beforeWriting ?? function (): void {
            },
            BeforeSheet::class => $this->beforeSheet ?? function (): void {
            },
            AfterSheet::class => $this->afterSheet ?? function (): void {
            },
        ];
    }
}
