<?php
namespace Maatwebsite\Excel\Tests\Data\Stubs\Database\Factories;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

class GroupFactory extends Factory {
    protected $model = Group::class;

    public function definition() {
        return [
            'name' => $this->faker->word,
        ];
    }
}