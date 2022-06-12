<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Events\BeforeChunk;
use Maatwebsite\Excel\Jobs\ExtendedQueueable;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class QueuedExportWithStaticEvents implements WithEvents, WithCustomChunkSize, FromQuery
{
    use Exportable, ExtendedQueueable, RegistersEventListeners;

    /**
     * @var callable
     */
    public static $beforeExport;

    /**
     * @var callable
     */
    public static $beforeWriting;

    /**
     * @var callable
     */
    public static $beforeSheet;

    /**
     * @var callable
     */
    public static $afterSheet;

    /**
     * @var callable
     */
    public static $beforeChunk;

    /**
     * @var callable
     */
    public static $afterChunk;

    public static function beforeExport()
    {
        (static::$beforeExport)(...func_get_args());
    }

    public static function beforeWriting()
    {
        (static::$beforeWriting)(...func_get_args());
    }

    public static function beforeSheet()
    {
        (static::$beforeSheet)(...func_get_args());
    }

    public static function afterSheet()
    {
        (static::$afterSheet)(...func_get_args());
    }

    public static function beforeChunk()
    {
        (static::$beforeChunk)(...func_get_args());
    }

    public static function afterChunk()
    {
        (static::$afterChunk)(...func_get_args());
    }

    /**
     * @return Builder|EloquentBuilder|Relation
     */
    public function query()
    {
        return User::query();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 10;
    }
}