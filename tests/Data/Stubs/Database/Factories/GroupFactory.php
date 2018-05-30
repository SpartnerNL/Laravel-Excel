<?php

use Faker\Generator as Faker;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/
$factory->define(Group::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
    ];
});
