<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Language;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    protected static function generateName(): array
    {
        return [
            'machine' => 'myconlang',
            'human' => 'My Conglang'
        ];
    }

    public function definition(): array
    {
        $name = self::generateName();

        return [
            'machine_name' => $name['machine'],
            'name' => $name['human'],
        ];
    }
}