<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Subsense;

class SubsenseFactory extends Factory
{
    protected $model = Subsense::class;

    public function definition(): array
    {
        return [
            'body' => "<p>" . $this->faker->sentence() . "</p>",
        ];
    }
}