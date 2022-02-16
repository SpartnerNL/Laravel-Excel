<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs\Database;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Factories\GroupFactory;

class Group extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    protected static function newFactory()
    {
        return new GroupFactory();
    }
}
