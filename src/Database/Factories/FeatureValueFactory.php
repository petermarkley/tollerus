<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\FeatureValue;

class FeatureValueFactory extends Factory
{
    protected $model = FeatureValue::class;

    public function definition(): array
    {
        return [
            'name' => 'subjective',
        ];
    }
}