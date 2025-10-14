<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Lexeme;
use PeterMarkley\Tollerus\Models\Sense;
use PeterMarkley\Tollerus\Models\Subsense;

class LexemeFactory extends Factory
{
    protected $model = Lexeme::class;

    public function definition(): array
    {
        return [];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Lexeme $lexeme) {
            // Pick a random number of senses, weighted toward $min
            $min = 1; $max = 5;
            $randFloat = (float)($this->faker->randomFloat(6, 0, 1));
            $senseNum = (int)round(pow($randFloat,2)*($max-$min)+$min);

            // Generate senses
            Sense::factory()
                ->for($lexeme)
                ->count($senseNum)
                ->state(new Sequence(fn($seq)=>['num'=>$seq->index]))
                ->has(Subsense::factory()
                    ->count(( ((bool)mt_rand(0,1)) ? mt_rand(1,4) : 0 ))
                    ->state(new Sequence(fn($seq)=>['num'=>$seq->index]))
                )->create();
        });
    }
}