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
    /**
     * This is the file input; it won't change throughout
     * the seeder's lifespan.
     */
    protected string $inflectionsFilePath;
    protected array $mainFilePaths;
    protected \SimpleXMLElement $inflectionsFile;

    /**
     * These are basically bookmarks to keep our place as
     * we go, without having to re-query and re-parse
     * things.
     */
    protected \SimpleXMLElement $currentConfXML;
    protected Language $currentLang;
    protected int $currentFileKey;
    protected Neography $currentNeo;

    /**
     * These are caches used to signal whether something
     * exists or needs to be created.
     */
    protected array $currentFeatures;
    protected array $currentClasses;

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
            $this->inflectionsFile = simplexml_load_file($this->inflectionsFilePath);
            if ($this->inflectionsFile === false) {
                throw new \RuntimeException("simplexml_load_file() failed on " . $this->inflectionsFilePath);
            }
        } else {
            $this->inflectionsFile = null;
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
            var_dump($this->inflectionsFile->language[0]->group[0]->list->class[0]['inflected']);
            return;
            $this->readLanguage($langXML);
        }
    }

    /**
     * One of the most fundamental differences between the old and new schemas
     * is that the old one hard-coded an exhaustive list of grammatical features
     * into the database, from ALL languages in the system. Consequently, there
     * was no authoritative list of grammar features inside the language data.
     *
     * Therefore we now need to reconstruct that list by registering each feature
     * as we encounter it, whether in the inflection config or in the dictionary
     * entries.
     */
    protected function addFeatureIfNew(
        WordClassGroup $wordClassGroup,
        string $featureName,
        string $valueName
    ): array
    {
        // Does this feature exist yet?
        if (!isset($this->currentFeatures[$featureName])) {
            // No, we need to add it
            $featureModel = new Feature();
            $featureModel->word_class_group_id = $wordClassGroup->id;
            $featureModel->name = $featureName;
            $featureModel->save();
            $this->currentFeature[$featureName] = [
                'model' => $featureModel,
                'featureValues' => []
            ];
        }
        // Does this feature value exist yet?
        if (!isset($this->currentFeatures[$featureName]['featureValues'][$valueName])) {
            // No, we need to add it
            $valueModel = new Value();
            $valueModel->feature_id = $this->currentFeatures[$featureName]['model']->id;
            $valueModel->name = $valueName;
            $valueModel->save();
            $this->currentFeatures[$featureName]['featureValues'][$valueName] = $valueModel;
        }
        // Bundle the models for use by the calling method
        return [
            'feature' => $this->currentFeatures[$featureName]['model'],
            'value' => $this->currentFeatures[$featureName]['featureValues'][$valueName]
        ];
    }

    /**
     * Parse a <dictionary/> XML element into a Language model
     */
    protected function readLanguage(SimpleXMLElement $langXML): void
    {
        $this->currentLang = new Language();
        $this->currentFeatures = [];
        // Find machine-friendly name for this language
        if (!isset($langXML['language']) || empty($langXML['language'])) {
            throw new \RuntimeException("No machine-friendly dictionary name in file '${this->mainFilePaths[$this->currentFileKey]}'");
        }
        $this->currentLang->machine_name = $langXML['language'];
        // Find the inflection config for this language
        $this->currentConfXML = null;
        if ($this->inflectionsFile !== null) {
            foreach ($this->inflectionsFile->language as $langConfXML) {
                if ($langConfXML['name'] == $this->currentLang->machine_name) {
                    $this->currentConfXML = $langConfXML;
                    break;
                }
            }
        }
        // Copy language properties
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
        // Save model
        $this->currentLang->save();
        // Read neographies in this dictionary file
        foreach ($langXML->scripts->script as $neoXML) {
            $this->readNeography($neoXML);
        }
        // Initialize caches
        $this->currentFeatures = [];
        $this->currentClasses = [];
        // Read through word class groups in the conf file
        if ($this->currentConfXML !== null) {
            foreach ($this->currentConfXML->group as $groupXML) {
                $this->readWordClassGroup($groupXML);
            }
        }
        // Read through main dictionary
        foreach ($langXML->data->entry as $entryXML) {
            $this->readEntry($entryXML);
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
        $this->currentNeo = Neography::where('machine_name', $neoName)->first();
        // If none found, create one
        if (!($this->currentNeo instanceof Neography)) {
            $this->currentNeo = new Neography();
            $this->currentNeo->machine_name = $neoName;
            if (isset($neoXML['human'])) {
                $this->currentNeo->name = $neoXML['human'];
            }
            if (isset($neoXML['svg'])) {
                $fontFile = self::readFontFile(dirname($this->mainFilePaths[$mainFileKey]) . $neoXML['svg']);
                $this->currentNeo->font_svg = $fontFile;
            }
            if (isset($neoXML['ttf'])) {
                $fontFile = self::readFontFile(dirname($this->mainFilePaths[$mainFileKey]) . $neoXML['ttf']);
                $this->currentNeo->font_ttf = $fontFile;
            }
            $this->currentNeo->save();
            foreach ($neoXML->section as $position => $neoSectXML) {
                $this->readNeographySection(
                    neoSectXML: $neoSectXML,
                    position: $position
                );
            }
        }
        // Check if this neography is the language's primary one
        if (isset($neoXML['primary']) && filter_var($neoXML['primary'], FILTER_VALIDATE_BOOLEAN)) {
            $this->currentLang->primary_neography = $this->currentNeo->id;
            $this->currentLang->save();
        }
        // Add connection between neography and language
        $pivot = new LanguageNeography([
            'language_id' => $this->currentLang->id,
            'neography_id' => $this->currentNeo->id,
        ]);
        $pivot->save();
    }

    /**
     * Parse a <section/> XML element into a NeographySection model
     */
    protected function readNeographySection(
        SimpleXMLElement $neoSectXML,
        int $position
    ): void
    {
        $neoSectModel = new NeographySection();
        $neoSectModel->neography_id = $this->currentNeo->id;
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
        $this->readNeographyGlyphGroup(
            neoSectModel: $neoSectModel,
            dataXML: $neoSectXML->data
        );
    }

    /**
     * Parse a child of the neography <data/> XML element into a NeographyGlyphGroup model
     */
    protected function readNeographyGlyphGroup(
        NeographySection $neoSectModel,
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
                $this->readNeographyGlyph(
                    glyphGroupModel: $glyphGroupModel,
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
                    $this->readNeographyGlyph(
                        glyphGroupModel: $glyphGroupModel,
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
    protected function readNeographyGlyph(
        NeographyGlyphGroup $glyphGroupModel,
        SimpleXMLElement $glyphXML,
        int $position
    ): void
    {
        $glyphModel = new NeographyGlyph();
        if (isset($glyphXML['id'])) {
            $glyphModel->global_id = $glyphXML['id'];
        }
        $glyphModel->group_id = $glyphGroupModel->id;
        $glyphModel->neography_id = $this->currentNeo->id;
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
        if (isset($glyphXML->pronunciation->{$this->currentNeo->machine_name})) {
            $glyphModel->pronunciation_native = $glyphXML->pronunciation->{$this->currentNeo->machine_name}->__toString();
        }
        if (isset($glyphXML->note)) {
            $glyphModel->note = $glyphXML->note->__toString();
        }
        $glyphModel->save();
    }

    /**
     * Parse a <group/> XML element in the inflections config into a WordClassGroup model
     */
    protected function readWordClassGroup(SimpleXMLElement $groupXML): void
    {
        $groupModel = new WordClassGroup();
        $groupModel->language_id = $this->currentLang->id;
        $groupModel->inflected = (
            isset($groupXML->list->class[0]['inflected']) &&
            filter_var($groupXML->list->class[0]['inflected'], FILTER_VALIDATE_BOOLEAN)
        );
        $groupModel->save();
        // Read through word classes in this group
        foreach ($groupXML->list->class as $classXML) {
            $this->readWordClass(
                groupModel: $groupModel,
                classXML: $classXML
            );
        }
        // Read through display tables in this group
        foreach ($groupXML->layout->table as $position => $tableXML) {
            $this->readDisplayTable(
                groupModel: $groupModel,
                tableXML: $tableXML,
                position: $position
            );
        }
    }

    /**
     * Parse a <class/> XML element in the inflections config into a WordClass model
     */
    protected function readWordClass(
        WordClassGroup $groupModel,
        SimpleXMLElement $classXML
    ): void
    {
        $classModel = new WordClass();
        $classModel->group_id = $groupModel->id;
        $classModel->language_id = $this->currentLang->id;
        if (!isset($classXML['name']) || empty($classXML['name'])) {
            throw new \RuntimeException("There's a word class with no name in file '${this->inflectionsFilePath}'");
        }
        $classModel->name = $classXML['name'];
        $classModel->save();
        /**
         * Later when we're reading word entries from the dictionary,
         * we can't assume that there was an inflections file provided
         * or therefore word classes already registered. We will need
         * some way to tell if a given word class exists or must be
         * created (preferrably without querying the database every
         * time).
         *
         * Keeping this record is how we'll know.
         */
        $this->currentClasses[$classModel->name] = [
            'group' => $groupModel,
            'class' => $classModel
        ];
    }

    /**
     * Parse a <table/> XML element in the inflections config into a DisplayTable model
     */
    protected function readDisplayTable(
        WordClassGroup $groupModel,
        SimpleXMLElement $tableXML,
        int $position
    ): void
    {
        $tableModel = new DisplayTable();
        $tableModel->word_class_group_id = $groupModel->id;
        $tableModel->position = $position;
        if (isset($tableXML['label'])) {
            $tableModel->label = $tableXML['label'];
        }
        if (isset($tableXML['stack'])) {
            $tableModel->stack = filter_var(
                $tableXML['stack'],
                FILTER_VALIDATE_BOOLEAN
            );
        }
        if (isset($tableXML['align_on_stack'])) {
            $tableModel->align_on_stack = filter_var(
                $tableXML['align_on_stack'],
                FILTER_VALIDATE_BOOLEAN
            );
        }
        if (isset($tableXML['fold'])) {
            $tableModel->table_fold = filter_var(
                $tableXML['fold'],
                FILTER_VALIDATE_BOOLEAN
            );
        }
        if (isset($tableXML->rows['fold'])) {
            $tableModel->rows_fold = filter_var(
                $tableXML->rows['fold'],
                FILTER_VALIDATE_BOOLEAN
            );
        }
        $tableModel->save();
        // Read through filters for this display table
        foreach ($tableXML->filter->inflect as $filterXML) {
            $array = $this->addFeatureIfNew(
                wordClassGroup: $groupModel,
                featureName: $filterXML['dimension'],
                valueName: $filterXML['value']
            );
            [
                'feature' => $featureModel,
                'value' => $valueModel
            ] = $array;
            // Add connection between disp table and feature values
            $pivot = new DisplayTableFilter([
                'disp_table_id' => $tableModel->id,
                'feature_id' => $featureModel->id,
                'value_id' => $valueModel->id,
            ]);
            $pivot->save();
        }
        // Read through rows for this display table
        foreach ($tableXML->rows->row as $rowPosition => $rowXML) {
            $this->readDisplayTableRow(
                groupModel: $groupModel,
                tableModel: $tableModel,
                rowXML: $rowXML,
                position: $rowPosition
            );
        }
    }

    /**
     * Parse a <row/> XML element in the inflections config into a DisplayTableRow model
     */
    protected function readDisplayTableRow(
        WordClassGroup $groupModel,
        DisplayTable $tableModel,
        SimpleXMLElement $rowXML,
        int $position
    ): void
    {
        $rowModel = new DisplayTableRow();
        $rowModel->disp_table_id = $tableModel->id;
        if (!isset($rowXML['label']) || empty($rowXML['label'])) {
            throw new \RuntimeException("There's a table row with no label in file '${this->inflectionsFilePath}'");
        }
        $rowModel->label = $rowXML['label'];
        $rowModel->label_brief = $rowXML['brief'];
        $rowModel->position = $position;
        $rowModel->save();
        // Read through filters for this table row
        foreach ($rowXML->filter->inflect as $filterXML) {
            $array = $this->addFeatureIfNew(
                wordClassGroup: $groupModel,
                featureName: $filterXML['dimension'],
                valueName: $filterXML['value']
            );
            [
                'feature' => $featureModel,
                'value' => $valueModel
            ] = $array;
            // Add connection between disp table and feature values
            $pivot = new DisplayTableRowFilter([
                'disp_table_row_id' => $rowModel->id,
                'feature_id' => $featureModel->id,
                'value_id' => $valueModel->id,
            ]);
            $pivot->save();
        }
    }

    /**
     * Parse a dictionary <entry/> XML element into an Entry model
     */
    protected function readEntry(SimpleXMLElement $entryXML): void
    {
        $entryModel = new Entry();
        if (isset($entryXML['id'])) {
            $entryModel->global_id = $entryXML['id'];
        }
        $entryModel->language_id = $this->currentLang->id;
        if (isset($entryXML->etym)) {
            $domNode = dom_import_simplexml($entryXML->etym);
            $entryModel->etym = collect($domNode->childNodes)
                ->map(function ($item) use ($domNode) {
                    return $domNode->ownerDocument->saveXML($item);
                })
                ->implode('');
        }
        $entryModel->save();
        // Read through lexemes
        foreach ($entryXML->class as $position => $lexemeXML) {
            $this->readLexeme(
                entryModel: $entryModel,
                lexemeXML: $lexemeXML,
                position: $position
            );
        }
    }

    /**
     * Parse a dictionary <class/> XML element into a Lexeme model
     */
    protected function readLexeme(
        Entry $entryModel,
        SimpleXMLElement $lexemeXML,
        int $position
    ): void
    {}
}
