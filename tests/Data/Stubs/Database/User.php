<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Engines\DatabaseEngine;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Searchable;

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
    protected $hidden = ['password', 'email_verified_at', 'options'];

    /**
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function searchableUsing(): Engine
    {
        return new DatabaseEngine();
    }
}
