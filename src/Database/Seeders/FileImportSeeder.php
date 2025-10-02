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
            self::readLanguage($langXML, $key);
        }
    }

    /**
     * Parse a <dictionary/> XML element into a Language model
     */
    protected static function readLanguage(
        SimpleXMLElement $langXML,
        string $mainFileKey = ''
    ): void
    {
        $langModel = new Language();
        if (!isset($langXML['language']) || empty($langXML['language'])) {
            throw new \RuntimeException("No machine-friendly dictionary name in file '${this->mainFilePaths[$mainFileKey]}'");
        }
        $langModel->machine_name = $langXML['language'];
        if (isset($langXML['lang_human'])) {
            $langModel->name = $langXML['lang_human'];
        }
        if (isset($langXML['title_short'])) {
            $langModel->dict_title = $langXML['title_short'];
        }
        if (isset($langXML['title_long'])) {
            $langModel->dict_title_full = $langXML['title_long'];
        }
        if (isset($langXML['author'])) {
            $langModel->dict_author = $langXML['author'];
        }
        if (isset($langXML->intro)) {
            $langModel->intro = collect($langXML->intro->children())
                ->map(fn($item) => $item->asXML())
                ->implode('');
        }
        $langModel->save();
        foreach ($langXML->scripts->script as $neoXML) {
            self::readNeography($langModel, $langXML, $mainFileKey);
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
     * Parse a <script/> XML element into a Neography model
     */
    protected static function readNeography(
        Language $langModel,
        SimpleXMLElement $neoXML,
        string $mainFileKey = ''
    ): void
    {
        if (!isset($neoXML['name']) || empty($neoXML['name'])) {
            throw new \RuntimeException("There's a script/neography with no machine-friendly name in file '${this->mainFilePaths[$mainFileKey]}'");
        }
        $neoName = $neoXML['name'];
        // Check for existing neography by this name
        $neoModel = Neography::where('machine_name', $neoName)->first();
        // If none found, create one
        if (!($neoModel instanceof Neography::class)) {
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
                self::readNeographySection($neoModel, $neoSectXML, $position, $mainFileKey);
            }
        }
        $pivot = new LanguageNeography([
            'language_id' => $langModel->id,
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
        int $position,
        string $mainFileKey = ''
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
        if (isset($neoSectXML->data->)) {}
    }

    /**
     * Parse a neography <entry/> XML element into a NeographyGlyph model
     */
    protected static function readNeographyGlyph(
        NeographySection $neoSectModel,
        SimpleXMLElement $glyphXML,
        NeographyGlyphType $type,
        int $position,
        string $mainFileKey = ''
    ): void
    {
    }
}
