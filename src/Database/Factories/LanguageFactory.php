<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\Pivots\LanguageNeography;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    /**
     * This will allow easily passing the Language name to the Neography
     */
    public function withNeography(): static
    {
        return $this->afterCreating(function (Language $langModel) {
            // Create the Neography, with custom name
            $neoModel = Neography::factory()
                ->withExtra(
                    machineName: $langModel->machine_name,
                    name: $langModel->name,
                    num: mt_rand(15, 30),
                    mix: ! (bool) mt_rand(0,2), // true 1/3rd of the time
                )->create();
            // Add connection between Neography and Language
            $pivot = new LanguageNeography([
                'language_id' => $langModel->id,
                'neography_id' => $neoModel->id,
            ]);
            $pivot->save();
            // Mark Neography as the primary one
            $langModel->primary_neography = $neoModel->id;
            $langModel->save();
        });
    }

    protected static function generateName(): array
    {
        $prefixArray = [
            'con', 'ben', 'rap', 'bud', 'tak',
            'lop', 'kran', 'sop', 'bid', 'wag',
            'gas', 'his', 'drip', 'dis', 'val',
            'yup',
        ];
        $middleArray = [
            'nex', 'soob', 'kran', 'yart', 'rud',
            'beb', 'fidd', 'blatt', 'jiss', 'frass',
            'puck', 'bart', 'goy', 'flep', 'sitt',
            'foy', 'lurt', 'hoy', 'roy', 'lov',
            'vonn', 'vegg', 'varch', 'cratch', 'gloob',
            'darf', 'sov', 'tar', 'yem', 'yuss',
            'vass', 'boos', 'soos', 'biss',
        ];
        $suffixArray = [
            ['truncate' => false, 'suffix' => 'ian'],
            ['truncate' => false, 'suffix' => 'ese'],
            ['truncate' => false, 'suffix' => 'ish'],
            ['truncate' => true, 'suffix' => 'ian'],
            ['truncate' => true, 'suffix' => 'ese'],
            ['truncate' => true, 'suffix' => 'ish'],
            ['truncate' => true, 'suffix' => 'ench'],
            ['truncate' => true, 'suffix' => 'oonch'],
        ];
        $suffix = $suffixArray[array_rand($suffixArray)];
        $middle = $middleArray[array_rand($middleArray)];
        $prefix = $prefixArray[array_rand($prefixArray)];

        /**
         * If the suffix says to truncate, we are going to remove
         * the coda and nucleus of the middle syllable, so it
         * just becomes the beginning of the suffix.
         */
        if ($suffix['truncate']) {
            /**
             * Our middle syllables never have more than 2 consonants at the
             * beginning, and they always have at least 1. So we can just test
             * the 2nd character to see what length we need to cut to.
             */
            $c = $middle[1];
            $isVowel = ($c=='a' || $c=='e' || $c=='i' || $c=='o' || $c=='u');
            $len = ($isVowel? 1 : 2);
            $middle = substr($middle,0,$len);
        }

        $name = $prefix . $middle . $suffix['suffix'];

        return [
            'machine' => strtolower($name),
            'human' => ucfirst($name)
        ];
    }

    public function definition(): array
    {
        // Generate language name
        $name = self::generateName();

        // Generate language intro text
        $intro = collect($this->faker->paragraphs(3))
            ->map(function ($item) {
                return "\t<p>" . $item . "</p>\n";
            })
            ->implode("");
        $intro = "<div>\n" . $intro . "</div>\n";

        return [
            'machine_name' => $name['machine'],
            'name' => $name['human'],
            'dict_title' => $name['human'] . " Dictionary",
            'dict_title_full' => "English Dictionary of the " . $name['human'] . " Language",
            'intro' => $intro,
        ];
    }
}