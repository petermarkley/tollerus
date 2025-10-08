<?php

namespace PeterMarkley\Tollerus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use PeterMarkley\Tollerus\Enums\NeographySectionType;
use PeterMarkley\Tollerus\Enums\NeographyGlyphType;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\NeographySection;
use PeterMarkley\Tollerus\Models\NeographyGlyphGroup;
use PeterMarkley\Tollerus\Models\NeographyGlyph;

class NeographyFactory extends Factory
{
    protected $model = Neography::class;

    /**
     * Define model values
     */
    public function definition(): array
    {
        return [
            'machine_name' => 'myneography',
            'name' => 'My Neography',
        ];
    }

    /**
     * If the calling context passes us a name, that unlocks the ability
     * to create a full, richly coordinated dataset of child objects.
     */
    public function withExtra(
        string $machineName,
        string $name,
        int $num = 20,
        bool $mix = false,
    ): static
    {
        // Step 1: Use the neography name to generate a glyph set and font.
        $glyphGroups = self::generateGlyphs(
            machineName: $machineName,
            name: $name,
            num: $num,
            mix: $mix,
        );
        $fontSVG = self::formatGlyphs(
            machineName: $machineName,
            name: $name,
            glyphGroups: $glyphGroups
        );
        // Step 2: Save appropriate parts of this to the Neography model.
        $factory = $this->state([
            'machine_name' => $machineName,
            'name' => $name,
            'font_svg' => $fontSVG,
        ]);
        // Step 3: Set hook to make child factories.
        return $factory->afterCreating(
            function (Neography $neoModel)
            use (
                $machineName,
                $name,
                $glyphGroups,
            ) {
                // Make section
                $neoSectModel = NeographySection::factory()
                    ->for($neoModel, 'neography')
                    ->create([
                        'type' => NeographySectionType::Alphabet,
                        'name' => $name . " Alphabet",
                        'position' => 0,
                    ]);
                // Make glyph groups
                foreach ($glyphGroups as $key => $glyphs) {
                    $groupModel = NeographyGlyphGroup::factory()
                        ->for($neoSectModel, 'section')
                        ->create([
                            'type' => NeographyGlyphType::Symbol,
                            'position' => $key,
                        ]);
                    // Make glyphs
                    foreach ($glyphs as $key => $glyph) {
                        $glyph = NeographyGlyph::factory()
                            ->for($neoModel, 'neography')
                            ->for($groupModel, 'group')
                            ->create([
                                'position' => $key,
                                'glyph' => $glyph['codepoint'],
                                'roman' => $glyph['roman'],
                                'phonemic' => $glyph['phonemic'],
                            ]);
                    };
                };
            }
        );
    }

    /**
     * This algorithm generates a semi-realistic set of glyphs
     * with no duplicates.
     */
    protected static function generateGlyph(): array
    {
        $vector = "(fixme vector data)";
        $horizAdv = 1000;
        return [
            'd' => $vector,
            'horizAdv' => $horizAdv,
        ];
    }
    protected static function generateGlyphs(
        string $machineName,
        string $name,
        int $num = 20,
        bool $mix = false,
    ): array
    {
        /**
         * Decide the list of codepoints
         */
        $first = 0xF2C00;
        $last = $first + $num - 1;
        $codepoints = collect(range($first, $last))
            ->map(
                fn($ch) => mb_chr($ch,'UTF-8')
            )
            ->toArray();
        
        /**
         * Generate vectors
         */
        $vectors = collect($codepoints)
            ->map(fn() => self::generateGlyph());
        
        /**
         * Decide the list of sounds
         */
        // IPA symbols
        $consonants = [
            'm̥','m','ɱ̊','ɱ','n̼','n̪̊','n̪','n̥','n','n̠̊','n̠','ɳ̊','ɳ','ɲ̊','ɲ','ŋ̊','ŋ','ɴ̥','ɴ',
            'p','b','p̪','b̪','t̼','d̼','t̪','d̪','t','d','ʈ','ɖ','c','ɟ','k','ɡ','q','ɢ','ʡ','ʔ',
            's̪','z̪','s','z','ʃ','ʒ','ʂ','ʐ','ɕ','ʑ',
            'ɸ','β','f','v','θ̼','ð̼','θ','ð','θ̠','ð̠','ɹ̠̊˔','ɹ̠˔','ɻ̊˔','ɻ˔','ç','ʝ','x','ɣ','χ','ʁ','ħ','ʕ','h','ɦ',
            'β̞','ʋ','ð̞','ɹ','ɹ̠','ɻ','j','ɰ','ʁ̞','ʔ̞',
            'ⱱ̟','ⱱ','ɾ̼','ɾ̥','ɾ','ɽ̊','ɽ','ɢ̆','ʡ̮',
            'ʙ̥','ʙ','r̥','r','r̠','ɽ̊r̥','ɽr','ʀ̥','ʀ','ʜ','ʢ',
            'ɬ̪','ɬ','ɮ','ꞎ','𝼅','𝼆','ʎ̝','𝼄','ʟ̝',
            'l̪','l̥','l','l̠','ɭ̊','ɭ','ʎ̥','ʎ','ʟ̥','ʟ','ʟ̠',
            'ɺ̥','ɺ','𝼈̊','𝼈','ʎ̮','ʟ̆'
        ];
        $vowels = [
            'i','y','ɨ','ʉ','ɯ','u',
            'ɪ','ʏ','ʊ',
            'e','ø','ɘ','ɵ','ɤ','o',
            'e̞','ø̞','ə','ɤ̞','o̞',
            'ɛ','œ','ɜ','ɞ','ʌ','ɔ',
            'æ','ɐ',
            'a','ɶ','ä','ɑ','ɒ'
        ];
        /**
         * Sloppy romanized equivalents of the above IPA sounds ...
         *
         * A real conlang would devise its own tailored transliteration scheme
         * based on the range of phonemes present in the language, to optimally
         * balance orthography vs. fluency in the target (i.e. Roman) alphabet.
         *
         * For a randomized model factory just for demo/dev/testing purposes,
         * that's overkill. That's why we're using this extremely dumb and sloppy
         * equivalence scheme.
         */
        $consonantsRoman = [
            'm','m','m','m','n','n','n','n','n','n','n','n','n','ny','ny','ng','ng','ng','ng',
            'p','b','p','b','t','d','t','d','t','d','t','d','ty','dy','k','g','q','g','qq','\'', 
            's','z','s','z','sh','zh','sh','zh','sh','zh',
            'ph','v','f','v','th','dh','th','dh','th','dh','r','r','r','r','ch','y','kh','gh','kh','gh','hh','gh','h','h',
            'w','v','dh','r','r','r','y','w','r','\'', 
            'v','v','r','r','r','r','r','g','q',
            'br','br','rr','rr','rr','rr','rr','rr','rr','hh','gh',
            'll','ll','lz','ll','ll','lz','ly','ly','l',
            'l','l','l','l','l','l','ly','ly','l','l','l',
            'lr','lr','ly','ly','ly','l'
        ];
        $vowelsRoman = [
            'i','y','i','u','u','u',
            'ih','yh','uh',
            'e','oe','e','oe','o','o',
            'e','oe','uh','o','o',
            'eh','oe','er','or','uh','aw',
            'ae','ah',
            'a','oe','a','ah','aw'
        ];
        // Let's settle on 25% vowels, 75% consonants.
        $vowelNum = (int)round($num/4);
        $consonantNum = $num - $vowelNum;
        // Choose sounds at random.
        $consonantIndices = array_rand($consonants, $consonantNum);
        $vowelIndices = array_rand($vowels, $vowelNum);
        shuffle($consonantIndices);
        shuffle($vowelIndices);

        /**
         * Build glyph groups
         */
        $glyphGroups = [];
        $consonantSounds = collect($consonantIndices)
            ->map(function ($i) use ($consonants, $consonantsRoman) {
                return [
                    'phonemic' => $consonants[$i],
                    'roman' => $consonantsRoman[$i],
                ];
            });
        $vowelSounds = collect($vowelIndices)
            ->map(function ($i) use ($vowels, $vowelsRoman) {
                return [
                    'phonemic' => $vowels[$i],
                    'roman' => $vowelsRoman[$i],
                ];
            });
        /**
         * The $mix flag decides whether the vowels and consonants are
         * grouped separately in this neography, or mixed together.
         */
        if ($mix) {
            // Shuffle the two arrays together
            $mixedSounds = $consonantSounds
                ->concat($vowelSounds)
                ->toArray();
            shuffle($mixedSounds);
            // Just one group with all sounds
            $glyphGroups[0] = collect($mixedSounds)
                ->map(function ($item, $key) use ($codepoints, $vectors) {
                    $item['codepoint'] = $codepoints[$key];
                    $item['vector'] = $vectors[$key];
                    return $item;
                })->toArray();
        } else {
            // Pick random order of the two groups
            $vowelsFirst = (bool) mt_rand(0,1);
            // Prevent sparse array when filling in random order
            $glyphGroups[0] = [];
            $glyphGroups[1] = [];
            // Fill consonants
            $offset = ($vowelsFirst ? $vowelNum : 0);
            $vector = self::generateGlyph();
            $glyphGroups[(int)$vowelsFirst] = $consonantSounds
                ->map(function ($item, $key) use ($codepoints, $offset, $vectors) {
                    $i = $key+$offset;
                    $item['codepoint'] = $codepoints[$i];
                    $item['vector'] = $vectors[$i];
                    return $item;
                })->toArray();
            // Fill vowels
            $offset = ($vowelsFirst ? 0 : $consonantNum);
            $vector = self::generateGlyph();
            $glyphGroups[(int)( ! $vowelsFirst)] = $vowelSounds
                ->map(function ($item, $key) use ($codepoints, $offset, $vectors) {
                    $i = $key+$offset;
                    $item['codepoint'] = $codepoints[$i];
                    $item['vector'] = $vectors[$i];
                    return $item;
                })->toArray();
        }
        return $glyphGroups;
    }

    /**
     * Format glyph set as SVG <glyph/> elements and compile into an SVG font file.
     */
    protected static function formatGlyph(string $machineName, array $glyphData): string
    {
        $glyphName = $machineName . ' letter ' . strtoupper($glyphData['roman']);
        $unicode = $glyphData['codepoint'];
        $d = $glyphData['vector']['d'];
        $horizAdv = $glyphData['vector']['horizAdv'];

        $output = <<<EOT
        <glyph glyph-name="$glyphName" unicode="$unicode" d="$d" horiz-adv-x="$horizAdv"/>
        EOT;
        return $output;
    }
    protected static function formatGlyphs(string $machineName, string $name, array $glyphGroups): string
    {
        $head = <<<EOT
        <svg>
        <font horiz-adv-x="1000">
        <font-face units-per-em="1000" font-family="$name"/>

        EOT;
        $body = collect($glyphGroups)
            ->flatten(1)
            ->map(function ($glyph) use ($machineName) {
                return self::formatGlyph($machineName, $glyph);
            })->implode("\n");
        $foot = <<<EOT
        </font>
        </svg>

        EOT;
        $output = $head . $body . "\n" . $foot;
        return $output;
    }
}