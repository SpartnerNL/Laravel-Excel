<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
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
}
