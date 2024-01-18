<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Engines\DatabaseEngine;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Engines\NullEngine;
use Laravel\Scout\Searchable;
use Maatwebsite\Excel\Tests\Concerns\FromQueryTest;
use Maatwebsite\Excel\Tests\QueuedQueryExportTest;

class User extends Model
{
    use Searchable;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];

    /**
     * @var array
     */
    protected $hidden = ['password', 'email_verified_at', 'options', 'group_id'];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Laravel Scout under <=8 provides only
     * — NullEngine, that is searches nothing and not applicable for tests and
     * — AlgoliaEngine, that is 3-d party dependent and not applicable for tests too.
     *
     * The only test-ready engine is DatabaseEngine that comes with Scout >8
     *
     * Then running tests we will examine engine and skip test until DatabaseEngine is provided.
     *
     * @see QueuedQueryExportTest::can_queue_scout_export()
     * @see FromQueryTest::can_export_from_scout()
     */
    public function searchableUsing(): Engine
    {
        return class_exists('\Laravel\Scout\Engines\DatabaseEngine') ? new DatabaseEngine() : new NullEngine();
    }
}
