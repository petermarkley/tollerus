<?php

namespace PeterMarkley\Tollerus\Database\Seeders;

use Illuminate\Database\Seeder;

use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Neography;

/**
 * This assumes that you already have a conlang (perhaps via
 * `DemoConlangSeeder` or `artisan tollerus:populate`), and also
 * an extra neography that is now enabled on that conlang (e.g.
 * by generating a 2nd demo conlang and enabling its neography
 * on the 1st demo conlang).
 *
 * This further assumes that you have a JSON file that defines a
 * mapping between the conlang sounds and the written glyphs of
 * the new neography, or basically the new spelling rules. It
 * expects a dumb 1:1 mapping; complex orthographies are not
 * supported by this seeder class.
 *
 * This JSON map file should have the form:
 * [
 *   {
 *     "lang_sound": {
 *       "transliterated": "rr",
 *       "phonemic": "r̠"
 *     },
 *     "written_glyph": {
 *       "transliterated": "r",
 *       "phonemic": "ɾ̥"
 *     }
 *   },
 *   { ... }
 * ]
 *
 * So basically an array of objects where each object represents
 * a language sound and the corresponding written glyph or
 * glyphs that should be used for it. You can re-use written
 * glyphs for multiple language sounds if needed, and not all
 * written glyphs need to be used. But each language sound needs
 * to be mapped to exactly one written glyph.
 *
 * (P.S. - If you paste the glyph sets of the old and new
 * neographies and explain the task, an LLM can create a nice
 * quick-and-dirty map file for demo/testing purposes.)
 */
class AddNewSpellingsToConlang extends Seeder
{
    private const string ENC = 'UTF-8';

    public function run(string $languageName, string $neographyName, string $mapFile): void
    {
        $language = Language::where('machine_name', $languageName)->first();
        $neography = Neography::where('machine_name', $neographyName)->first();
        $map = json_decode(file_get_contents($mapFile));

        foreach ($language->forms as $form) {
            $srcStr = $form->phonemic;
            $newGlyphs = [];
            while (mb_strlen($srcStr, self::ENC) > 0) {
                $match = collect($map)->first(fn ($s) => mb_strpos($srcStr, $s->lang_sound->phonemic, 0, self::ENC)===0);
                if ($match) {
                    $glyph = $neography->glyphs->firstWhere('phonemic', $match->written_glyph->phonemic);
                    $newGlyphs[] = $glyph;
                    $len = mb_strlen($glyph->phonemic, self::ENC);
                } else {
                    $len = 1;
                }
                $srcStr = mb_substr($srcStr, 1, null, self::ENC);
            }
            $newSpelling = collect($newGlyphs)->pluck('glyph')->implode('');
            $form->nativeSpellings()->create([
                'neography_id' => $neography->id,
                'spelling' => $newSpelling,
            ]);
        }
    }
}
