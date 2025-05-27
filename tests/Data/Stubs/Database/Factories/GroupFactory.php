<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
