<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\DisplayTableRow;

class DisplayTableRowFactory extends Factory
{
    protected $model = DisplayTableRow::class;

    public function definition(): array
    {
        return [
            'label' => 'noun case',
            'position' => 0,
        ];
    }
}