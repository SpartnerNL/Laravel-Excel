<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupedUser extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
