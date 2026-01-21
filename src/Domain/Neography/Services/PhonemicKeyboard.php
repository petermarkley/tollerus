<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Services;

use IntlChar; // This can be installed as `ext-intl`
// use Illuminate\Support\Facades\Cache;
use PeterMarkley\Tollerus\Models\NeographyGlyph;

final class PhonemicKeyboard
{
    // public const string CACHE_KEY  = 'tollerus:ipa-keyboard';
    private const string IPA_KEYBOARD_FILE = __DIR__.'/../../../../resources/data/ipa/keyboard.json';
    private const string ENC = 'UTF-8';

    public function load(): array
    {
        /**
         * Step 1: Read data file
         * ======================
         */
        if (!is_file(self::IPA_KEYBOARD_FILE)) {
            throw new \RuntimeException(__('tollerus::error.ipa_json_missing'));
        }
        $file = file_get_contents(self::IPA_KEYBOARD_FILE);
        if ($file === false) {
            throw new \RuntimeException(__('tollerus::error.ipa_json_read_error'));
        }
        $json = json_decode($file);
        if ($json === null) {
            throw new \RuntimeException(__('tollerus::error.ipa_json_read_error'));
        }

        /**
         * Step 2: Dereference localization keys
         * =====================================
         */
        $jsonTranslated = collect($json)->map(function ($glyph, $index) {
            $glyph->labelTranslated = __('tollerus::ipa.' . $glyph->label);
            $glyph->index = $index;
            return $glyph;
        });

        /**
         * Step 3: Handle user-inputted characters
         * =======================================
         *
         * For this, we'll actually start with the list of stock IPA
         * symbols because we need to sort it by largest first to
         * basically eager-match against the user input strings.
         */
        $jsonLargestToSmallest = $jsonTranslated->map(function ($glyph) {
            $glyph->mbLen = mb_strlen($glyph->glyph, self::ENC);
            $glyphChars = [];
            for ($i=0; $i < $glyph->mbLen; $i++) {
                $glyphChars[] = dechex(mb_ord(mb_substr($glyph->glyph, $i, 1, 'UTF-8'), 'UTF-8'));
            }
            $glyph->hex = implode(', ', $glyphChars);
            return $glyph;
        })->sortBy([
            // Within each size, maintain prior sequence
            ['mbLen', 'desc'],
            ['index', 'asc'],
        ])->values();
        // Fetch user-inputted chars
        $userInput = NeographyGlyph::query()->whereNotNull('phonemic')->pluck('phonemic');
        /**
         * This is where we match the IPA glyphs against user input, and
         * only keep those IPA glyphs that have one or more occurrences.
         *
         * Atypically, we also have to mutate the array of user input as
         * we go, to consume all occurrences of the matched portion. So we
         * pass `$userInput` by reference to the closure, and use an old-
         * school `for` loop for tighter control.
         */
        $jsonFiltered = $jsonLargestToSmallest->filter(function ($ipaGlyph) use (&$userInput) {
            $found = false;
            for ($i=0; $i <= $userInput->keys()->max(); $i++) {
                $userStr = $userInput->get($i);
                $pos = mb_strpos($userStr, $ipaGlyph->glyph, 0, self::ENC);
                if ($pos !== false) {
                    // Mark to keep this IPA glyph
                    $found = true;
                    // Remove it from the user input
                    if ($pos === 0 && mb_strlen($userStr, self::ENC) == $ipaGlyph->mbLen) {
                        /**
                         * Whole string matched, remove collection item...
                         *
                         * (This creates a sparse array, so our loop with
                         * `->get($i)` maintains continuity despite the
                         * holes we're creating.)
                         */
                        $userInput->forget($i);
                    } else {
                        // Substr matched, splice it out
                        $before = mb_substr($userStr, 0, $pos, self::ENC);
                        $after = mb_substr($userStr, ($pos + $ipaGlyph->mbLen), null, self::ENC);
                        $newStr = $before . $after;
                        $userInput = $userInput->replace([$i => $newStr]);
                    }
                }
            }
            return $found;
        })->values();
        /**
         * Now, for anything that remains of the user input,
         * we fragment to a flat character list ...
         */
        $userChars = $userInput->map(function ($str) {
            $strLen = mb_strlen($str, self::ENC);
            $strChars = [];
            for ($i=0; $i < $strLen; $i++) {
                $strChars[] = mb_substr($str, $i, 1, self::ENC);
            }
            return $strChars;
        })->flatten(1);
        /**
         * ... and assign appropriate metadata, then sort.
         */
        $userCharsClean = $userChars->map(function ($char) {
            $codepoint = (int)mb_ord($char, self::ENC);
            $label = null;
            $renderOnBase = false;
            /**
             * Check if `ext-intl` is installed so we can greatly
             * enrich the UX for external glyphs ...
             */
            if (class_exists(\IntlChar::class)) {
                $label = \IntlChar::charName($char, \IntlChar::UNICODE_CHAR_NAME);
                $renderOnBase = in_array(
                    \IntlChar::charType($char),
                    [
                        \IntlChar::CHAR_CATEGORY_NON_SPACING_MARK,
                        \IntlChar::CHAR_CATEGORY_COMBINING_SPACING_MARK,
                        \IntlChar::CHAR_CATEGORY_ENCLOSING_MARK,
                    ],
                    true
                );
            }
            /**
             * We need this to be an object so it matches the parsed JSON
             * in `$jsonFiltered`.
             */
            return json_decode(json_encode([
                'glyph' => $char,
                'labelTranslated' => $label,
                'render_on_base' => $renderOnBase,
                'codepoint' => $codepoint,
                'hex' => dechex($codepoint),
            ]));
        })->sortBy('codepoint')->values();
        /**
         * Concatenate extra glyphed at the end of the matched IPA glyphs
         */
        $canonical = $jsonFiltered
            ->concat($userCharsClean)
            ->values()
            ->toArray();

        /**
         * Step 4: Handle stock IPA charset
         * ================================
         */
        $categories = $jsonTranslated->pluck('category')->unique()->values();
        return [
            'canonical' => $canonical,
            'tabs' => $categories->map(function ($category) use ($jsonTranslated) {
                return [
                    'key' => $category,
                    'label' => __('tollerus::ipa.' . $category),
                    'glyphs' => $jsonTranslated->filter(fn ($g) => $g->category == $category)->values()->toArray(),
                ];
            })->toArray(),
        ];
    }
}

