<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Enums\NeographySectionType;
use PeterMarkley\Tollerus\Models\NeographySection;

class NeographySectionFactory extends Factory
{
    protected $model = NeographySection::class;

    public function definition(): array
    {
        // Generate language intro text
        $intro = collect($this->faker->paragraphs(3))
            ->map(function ($item) {
                return "\t<p>" . $item . "</p>\n";
            })
            ->implode("");
        $intro = "<div>\n" . $intro . "</div>\n";

        return [
            'type' => NeographySectionType::Alphabet,
            'name' => 'My Neography Alphabet',
            'intro' => $intro,
        ];
    }
}