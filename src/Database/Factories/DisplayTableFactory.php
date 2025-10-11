<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\DisplayTable;

class DisplayTableFactory extends Factory
{
    protected $model = DisplayTable::class;

    public function definition(): array
    {
        return [
            'label' => 'noun case',
            'position' => 0,
            'show_label' => true,
            'stack' => true,
            'align_on_stack' => false,
            'table_fold' => false,
            'rows_fold' => false
        ];
    }
}