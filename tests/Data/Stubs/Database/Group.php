<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs\Database;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Factories\GroupFactory;

/**
 * @property int $id
 * @property string $name
 * @property int $number_of_users
 */
class Group extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    protected static function newFactory(): GroupFactory
    {
        return GroupFactory::new();
    }
}
