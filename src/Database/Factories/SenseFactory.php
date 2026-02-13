<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Sense;
use PeterMarkley\Tollerus\Models\Subsense;

class SenseFactory extends Factory
{
    protected $model = Sense::class;

    public function definition(): array
    {
        return [
            'body' => "<p>" . $this->faker->sentence() . "</p>",
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Sense $sense) {
            // Pick a random number of subsenses, weighted toward $min
            $min = 0; $max = 2;
            $randFloat = (float)($this->faker->randomFloat(6, 0, 1));
            $subsenseNum = (int)round(pow($randFloat,2)*($max-$min)+$min);

            // Generate subsenses
            Subsense::factory()
                ->for($sense)
                ->count($subsenseNum)
                ->state(new Sequence(fn($seq)=>['num'=>$seq->index+1]))
                ->create();
        });
    }
}