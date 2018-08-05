<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Events\Event;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;

trait RegistersCustomConcerns
{
    /**
     * @var array
     */
    private static $eventMap = [
        BeforeWriting::class => Writer::class,
        BeforeExport::class  => Writer::class,
        BeforeSheet::class   => Sheet::class,
        AfterSheet::class    => Sheet::class,
    ];

    /**
     * @param string   $concern
     * @param callable $handler
     * @param string   $event
     */
    public static function extend(string $concern, callable $handler, string $event = BeforeWriting::class)
    {
        /** @var HasEventBus $delegate */
        $delegate = static::$eventMap[$event] ?? BeforeWriting::class;

        $delegate::listen($event, function (Event $event) use ($concern, $handler) {
            if ($event->appliesToConcern($concern)) {
                $handler($event->getConcernable(), $event->getDelegate());
            }
        });
    }
}
