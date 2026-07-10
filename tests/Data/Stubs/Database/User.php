<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs\Database;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Laravel\Scout\Engines\DatabaseEngine;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Searchable;
use Maatwebsite\Excel\Tests\Concerns\FromQueryTest;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Factories\UserFactory;
use Maatwebsite\Excel\Tests\QueuedQueryExportTest;
use Override;

/**
 * @property string $email
 * @property string $name
 * @property string $firstname
 * @property string $lastname
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Searchable;

    protected $guarded = [];

    protected $hidden = ['password', 'email_verified_at', 'options', 'group_id'];

    /**
     * @return BelongsToMany<Group, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * @return BelongsTo<Group, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @see QueuedQueryExportTest::can_queue_scout_export()
     * @see FromQueryTest::can_export_from_scout()
     */
    public function searchableUsing(): Engine
    {
        return new DatabaseEngine;
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }
}
