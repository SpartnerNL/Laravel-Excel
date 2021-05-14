<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Hash;

class User extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $hidden = ['password', 'email_verified_at'];

    /**
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public static function booting()
    {
        static::creating(function ($user) {
            $user->password ??= Hash::make('secret');
        });
    }
}
