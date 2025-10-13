<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Sense;

class SenseFactory extends Factory
{
    protected $model = Sense::class;

    public function definition(): array
    {
        return [
            'body' => "<p>" . $this->faker->sentence() . "</p>",
        ];
    }
}