<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\WordClass;

class WordClassFactory extends Factory
{
    protected $model = WordClass::class;

    public function definition(): array
    {
        return [
            'name' => 'noun',
        ];
    }
}