<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Post;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(5),
        'image' => $faker->imageUrl(),
        'link' => $faker->unique()->url,
        'source_id' => function () {
            return factory(App\Source::class)->create()->id;
        },
        'description' => $faker->text,
        'date' => now(),
    ];
});
