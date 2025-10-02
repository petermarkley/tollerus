<?php

namespace PeterMarkley\Tollerus\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

use PeterMarkley\Tollerus\Enums\NeographyGlyphType;
use PeterMarkley\Tollerus\Enums\NeographySectionType;
use PeterMarkley\Tollerus\Models\DisplayTable;
use PeterMarkley\Tollerus\Models\DisplayTableRow;
use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\GlobalId;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Lexeme;
use PeterMarkley\Tollerus\Models\NativeSpelling;
use PeterMarkley\Tollerus\Models\NeographyGlyph;
use PeterMarkley\Tollerus\Models\NeographyGlyphGroup;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\NeographySection;
use PeterMarkley\Tollerus\Models\Sense;
use PeterMarkley\Tollerus\Models\Subsense;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\WordClass;
use PeterMarkley\Tollerus\Models\Pivots\DisplayTableFilter;
use PeterMarkley\Tollerus\Models\Pivots\DisplayTableRowFilter;
use PeterMarkley\Tollerus\Models\Pivots\FormFeatureValue;
use PeterMarkley\Tollerus\Models\Pivots\LanguageNeography;

/**
 * This seeder imports data from the legacy Tollerus XML file format.
 *
 * To specify a file, run using:
 *
 *   php artisan tollerus:import [--infl=<FILE_1>] <FILE_2> ...
 *
 * where FILE_1 is the inflections XML, and subsequent files are the
 * main dictionary files. If no files are specified, it defaults to
 * "My Conlang" demo data.
 */
class FileImportSeeder extends Seeder
{
    protected string $inflectionsFilePath;
    protected array $mainFilePaths;

    protected Language $currentLang;
    protected int $currentFileKey;

    /**
     * Accept file paths when creating the seeder manually.
     */
    public function __construct(
        string $inflectionsFilePath = null,
        array $mainFilePaths = []
    )
    {
        /**
         * Only if no arguments are provided will it revert to the demo data
         */
        if (!$inflectionsFilePath && count($mainFilePaths) < 1) {
            $this->inflectionsFilePath = __DIR__.'/data/myconlang-inflections.xml';
            $this->mainFilePaths = [__DIR__.'/data/myconlang.xml'];
        } else {
            $this->inflectionsFilePath = $inflectionsFilePath;
            $this->mainFilePaths = $mainFilePaths;
        }
    }

    /**
     * Retrieve a font file
     */
    protected static function readFontFile(string $fontFilePath): string
    {
        $fontFile = file_get_contents($fontFilePath);
        if ($fontFile === false) {
            throw new \RuntimeException("file_get_contents() failed on " . $fontFile);
        }
        return $fontFile;
    }

    /**
     * Run the seeder
     */
    public function run(): void
    {
        // Check for & read inflections file
        if ($this->inflectionsFilePath) {
            $inflectionsFile = simplexml_load_file($this->inflectionsFilePath);
            if ($inflectionsFile === false) {
                throw new \RuntimeException("simplexml_load_file() failed on " . $this->inflectionsFilePath);
            }
        } else {
            $inflectionsFile = null;
        }
        // Check for & read main dictionary files
        $mainFiles = collect($this->mainFilePaths)
            ->map(function($item) {
                $xml = simplexml_load_file($item);
                if ($xml === false) {
                    throw new \RuntimeException("simplexml_load_file() failed on " . $item);
                }
                return $xml;
            });
        // Parse each dictionary file
        foreach ($mainFiles as $key => $langXML) {
            $this->currentFileKey = $key;
            var_dump(isset($langXML->scripts->script->section->data->symbols->entry[1]->glyph->base));
            return;
            $this->readLanguage($langXML);
        }
    }

    /**
     * Parse a <dictionary/> XML element into a Language model
     */
    protected function readLanguage(SimpleXMLElement $langXML): void
    {
        $this->currentLang = new Language();
        if (!isset($langXML['language']) || empty($langXML['language'])) {
            throw new \RuntimeException("No machine-friendly dictionary name in file '${this->mainFilePaths[$this->currentFileKey]}'");
        }
        $this->currentLang->machine_name = $langXML['language'];
        if (isset($langXML['lang_human'])) {
            $this->currentLang->name = $langXML['lang_human'];
        }
        if (isset($langXML['title_short'])) {
            $this->currentLang->dict_title = $langXML['title_short'];
        }
        if (isset($langXML['title_long'])) {
            $this->currentLang->dict_title_full = $langXML['title_long'];
        }
        if (isset($langXML['author'])) {
            $this->currentLang->dict_author = $langXML['author'];
        }
        if (isset($langXML->intro)) {
            $this->currentLang->intro = collect($langXML->intro->children())
                ->map(fn($item) => $item->asXML())
                ->implode('');
        }
        $this->currentLang->save();
        foreach ($langXML->scripts->script as $neoXML) {
            $this->readNeography($neoXML);
        }
    }

    /**
     * Parse a <script/> XML element into a Neography model
     */
    protected function readNeography(SimpleXMLElement $neoXML): void
    {
        if (!isset($neoXML['name']) || empty($neoXML['name'])) {
            throw new \RuntimeException("There's a script/neography with no machine-friendly name in file '${this->mainFilePaths[$this->currentFileKey]}'");
        }
        $neoName = $neoXML['name'];
        // Check for existing neography by this name
        $neoModel = Neography::where('machine_name', $neoName)->first();
        // If none found, create one
        if (!($neoModel instanceof Neography)) {
            $neoModel = new Neography();
            $neoModel->machine_name = $neoName;
            if (isset($neoXML['human'])) {
                $neoModel->name = $neoXML['human'];
            }
            if (isset($neoXML['svg'])) {
                $fontFile = self::readFontFile(dirname($this->mainFilePaths[$mainFileKey]) . $neoXML['svg']);
                $neoModel->font_svg = $fontFile;
            }
            if (isset($neoXML['ttf'])) {
                $fontFile = self::readFontFile(dirname($this->mainFilePaths[$mainFileKey]) . $neoXML['ttf']);
                $neoModel->font_ttf = $fontFile;
            }
            $neoModel->save();
            foreach ($neoXML->section as $position => $neoSectXML) {
                self::readNeographySection(
                    neoModel: $neoModel,
                    neoSectXML: $neoSectXML,
                    position: $position
                );
            }
        }
        // Check if this neography is the language's primary one
        if (isset($neoXML['primary']) && filter_var($neoXML['primary'], FILTER_VALIDATE_BOOLEAN)) {
            $this->currentLang->primary_neography = $neoModel->id;
            $this->currentLang->save();
        }
        // Add connection between neography and language
        $pivot = new LanguageNeography([
            'language_id' => $this->currentLang->id,
            'neography_id' => $neoModel->id,
        ]);
        $pivot->save();
    }

    /**
     * Parse a <section/> XML element into a NeographySection model
     */
    protected static function readNeographySection(
        Neography $neoModel,
        SimpleXMLElement $neoSectXML,
        int $position
    ): void
    {
        $neoSectModel = new NeographySection();
        $neoSectModel->neography_id = $neoModel->id;
        if (isset($neoSectXML['type'])) {
            $neoSectModel->type = NeographySectionType::tryFrom($neoSectXML['type']);
        }
        if (isset($neoSectXML['title'])) {
            $neoSectModel->name = $neoSectXML['title'];
        }
        if (isset($neoSectXML->intro)) {
            $neoSectModel->intro = collect($neoSectXML->intro->children())
                ->map(fn($item) => $item->asXML())
                ->implode('');
        }
        $neoSectModel->position = $position;
        $neoSectModel->save();
        self::readNeographyGlyphGroup(
            neoSectModel: $neoSectModel,
            neoModel: $neoModel,
            dataXML: $neoSectXML->data
        );
    }

    /**
     * Parse a child of the neography <data/> XML element into a NeographyGlyphGroup model
     */
    protected static function readNeographyGlyphGroup(
        NeographySection $neoSectModel,
        Neography $neoModel,
        SimpleXMLElement $dataXML
    ): void
    {
        if (isset($dataXML->entry)) {
            /**
             * If we have <entry/> elements directly under the <data/> element, that means
             * there's basically no group in the source XML and we need a dummy group
             */
            $glyphGroupModel = new NeographyGlyphGroup();
            $glyphGroupModel->section_id = $neoSectModel->id;
            $glyphGroupModel->type = null;
            $glyphGroupModel->position = 0;
            $glyphGroupModel->save();
            foreach ($dataXML->entry as $position => $glyphXML) {
                self::readNeographyGlyph(
                    glyphGroupModel: $glyphGroupModel,
                    neoModel: $neoModel,
                    glyphXML: $glyphXML,
                    position: $position
                );
            }
        } else {
            /**
             * However if we do NOT have <entry/> elements directly under the <data/> element,
             * that means we could have any number / combo of <symbols/> and <marks/> elements
             * which are explicit glyph groups in the source XML and must be handled thus.
             */
            foreach ($dataXML->children() as $groupPosition => $glyphGroupXML) {
                $glyphGroupModel = new NeographyGlyphGroup();
                $glyphGroupModel->section_id = $neoSectModel->id;
                $glyphGroupModel->type = match ($glyphGroupXML->getName()) {
                    'symbols' => NeographyGlyphType::from('symbol'),
                    'marks' => NeographyGlyphType::from('mark'),
                    default => null
                };
                $glyphGroupModel->position = $groupPosition;
                $glyphGroupModel->save();
                foreach ($glyphGroupXML->entry as $position => $glyphXML) {
                    self::readNeographyGlyph(
                        glyphGroupModel: $glyphGroupModel,
                        neoModel: $neoModel,
                        glyphXML: $glyphXML,
                        position: $position
                    );
                }
            }
        }
    }

    /**
     * Parse a neography <entry/> XML element into a NeographyGlyph model
     */
    protected static function readNeographyGlyph(
        NeographyGlyphGroup $glyphGroupModel,
        Neography $neoModel,
        SimpleXMLElement $glyphXML,
        int $position
    ): void
    {
        $glyphModel = new NeographyGlyph();
        if (isset($glyphXML['id'])) {
            $glyphModel->global_id = $glyphXML['id'];
        }
        $glyphModel->group_id = $glyphGroupModel->id;
        $glyphModel->neography_id = $neoModel->id;
        if (isset($glyphXML['order'])) {
            $glyphModel->position = (int)$glyphXML['order'];
        } else {
            $glyphModel->position = $position;
        }
        $glyphModel->render_base = isset($glyphXML->glyph->base);
        $glyphModel->glyph = $glyphXML->glyph->__toString();
        if (isset($glyphXML->roman)) {
            $glyphModel->roman = $glyphXML->roman->__toString();
        }
        if (isset($glyphXML->phonemic)) {
            $glyphModel->phonemic = $glyphXML->phonemic->__toString();
        }
        if (isset($glyphXML->pronunciation->roman)) {
            $glyphModel->pronunciation_roman = $glyphXML->pronunciation->roman->__toString();
        }
        if (isset($glyphXML->pronunciation->phonemic)) {
            $glyphModel->pronunciation_phonemic = $glyphXML->pronunciation->phonemic->__toString();
        }
        if (isset($glyphXML->pronunciation->{$neoModel->machine_name})) {
            $glyphModel->pronunciation_native = $glyphXML->pronunciation->{$neoModel->machine_name}->__toString();
        }
        if (isset($glyphXML->note)) {
            $glyphModel->note = $glyphXML->note->__toString();
        }
        $glyphModel->save();
    }
}
