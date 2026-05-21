<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Writer;

/**
 * @implements FromCollection<int, Collection<int, string>>
 */
class SheetWith100Rows implements FromCollection, ShouldAutoSize, WithEvents, WithTitle
{
    use Exportable, RegistersEventListeners;

    public function __construct(
        private string $title,
    ) {
    }

    /**
     * @return Collection<int, Collection<int, string>>
     */
    public function collection(): Collection
    {
        $collection = new Collection;
        for ($i = 0; $i < 100; $i++) {
            /** @var Collection<int, string> */
            $row = new Collection;
            for ($j = 0; $j < 5; $j++) {
                $row[] = $this->title() . '-' . $i . '-' . $j;
            }

            $collection->push($row);
        }

        return $collection;
    }

    public function title(): string
    {
        return $this->title;
    }

    public static function beforeWriting(BeforeWriting $event): void
    {
        TestCase::assertInstanceOf(Writer::class, $event->writer);
    }
}
