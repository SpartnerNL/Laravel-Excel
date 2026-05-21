<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeSheet;

class ImportWithEvents implements WithEvents
{
    use Importable;

    /**
     * @var ?callable
     */
    public $beforeImport;

    /**
     * @var ?callable
     */
    public $afterImport;

    /**
     * @var ?callable
     */
    public $beforeSheet;

    /**
     * @var ?callable
     */
    public $afterSheet;

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => $this->beforeImport ?? function (): void {
            },
            AfterImport::class => $this->afterImport ?? function (): void {
            },
            BeforeSheet::class => $this->beforeSheet ?? function (): void {
            },
            AfterSheet::class => $this->afterSheet ?? function (): void {
            },
        ];
    }
}
