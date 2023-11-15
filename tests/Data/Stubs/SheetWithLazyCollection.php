<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Writer;

class SheetWithLazyCollection implements FromCollection, WithTitle, ShouldAutoSize, WithEvents
{
    use Exportable, RegistersEventListeners;

    /**
     * @var string
     */
    private $title;

    /**
     * @param  string  $title
     */
    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return LazyCollection
     */
    public function collection()
    {
        $collection = LazyCollection::make(function () {
            for ($i = 0; $i < 100; $i++) {
                yield $this->title() . '-' . $i . '-' . 1;
                yield $this->title() . '-' . $i . '-' . 2;
                yield $this->title() . '-' . $i . '-' . 3;
            }

        });

        return $collection;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @param  BeforeWriting  $event
     */
    public static function beforeWriting(BeforeWriting $event)
    {
        TestCase::assertInstanceOf(Writer::class, $event->writer);
    }
}
