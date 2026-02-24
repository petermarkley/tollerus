<?php

namespace PeterMarkley\Tollerus\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

use PeterMarkley\Tollerus\Domain\Neography\Actions\BuildGlyphCanonicalRanks;
use PeterMarkley\Tollerus\Enums\NeographyGlyphType;
use PeterMarkley\Tollerus\Enums\NeographySectionType;
use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\GlobalId;
use PeterMarkley\Tollerus\Models\InflectionColumn;
use PeterMarkley\Tollerus\Models\InflectionRow;
use PeterMarkley\Tollerus\Models\InflectionTable;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Lexeme;
use PeterMarkley\Tollerus\Models\NativeSpelling;
use PeterMarkley\Tollerus\Models\NeographyGlyph;
use PeterMarkley\Tollerus\Models\NeographyGlyphGroup;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\NeographySection;
use PeterMarkley\Tollerus\Models\Sense;
use PeterMarkley\Tollerus\Models\Subsense;
use PeterMarkley\Tollerus\Models\WordClass;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\Pivots\FormFeatureValue;
use PeterMarkley\Tollerus\Models\Pivots\InflectionColumnFilter;
use PeterMarkley\Tollerus\Models\Pivots\InflectionRowFilter;
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
    protected string|null $inflectionsFilePath;
    protected array $mainFilePaths;
    protected $inflectionsFile;
    protected string|null $rootPath;

    /**
     * These are basically bookmarks to keep our place as
     * we go, without having to re-query and re-parse
     * things.
     */
    protected $currentConfXML;
    protected $currentLang;
    protected int $currentFileKey;
    protected $currentNeo;

    /**
     * These are caches used to signal whether something
     * exists or needs to be created.
     */
    protected array $currentFeatures;
    protected array $currentClasses;
    protected array $validNeos;

    /**
     * Accept file paths when creating the seeder manually.
     */
    public function __construct(
        string|null $inflectionsFilePath = '',
        array $mainFilePaths = [],
        string|null $rootPath = ''
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
        $this->rootPath = $rootPath;
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
        if (!empty($this->inflectionsFilePath)) {
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
            $this->readLanguage($langXML);
        }
        // Allow queued jobs again
        foreach (Neography::all() as $neography) {
            Neography::whereKey($neography->id)->update(['sort_keys_job_queued' => false]);
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
            $this->currentFeatures[$featureName] = [
                'model' => $featureModel,
                'featureValues' => []
            ];
        }
        // Does this feature value exist yet?
        if (!isset($this->currentFeatures[$featureName]['featureValues'][$valueName])) {
            // No, we need to add it
            $valueModel = new FeatureValue();
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
    protected function readLanguage(\SimpleXMLElement $langXML): void
    {
        $this->currentLang = new Language();
        $this->currentFeatures = [];
        // Find machine-friendly name for this language
        if (!isset($langXML['language']) || empty($langXML['language'])) {
            throw new \RuntimeException("No machine-friendly dictionary name in file '{$this->mainFilePaths[$this->currentFileKey]}'");
        }
        $this->currentLang->machine_name = $langXML['language']->__toString();
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
            $this->currentLang->name = $langXML['lang_human']->__toString();
        }
        if (isset($langXML['title_short'])) {
            $this->currentLang->dict_title = $langXML['title_short']->__toString();
        }
        if (isset($langXML['title_long'])) {
            $this->currentLang->dict_title_full = $langXML['title_long']->__toString();
        }
        if (isset($langXML['author'])) {
            $this->currentLang->dict_author = $langXML['author']->__toString();
        }
        // Save model
        $this->currentLang->save();
        // Read neographies in this dictionary file
        $this->validNeos = [];
        foreach ($langXML->scripts->script as $neoXML) {
            $this->readNeography($neoXML);
        }
        $this->currentNeo = null;
        // Parse intro
        if (isset($langXML->intro)) {
            $this->currentLang->intro = $this->parseBodyText($langXML->intro);
            $this->currentLang->save();
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
    protected function readNeography(\SimpleXMLElement $neoXML): void
    {
        if (!isset($neoXML['name']) || empty($neoXML['name'])) {
            throw new \RuntimeException("There's a script/neography with no machine-friendly name in file '{$this->mainFilePaths[$this->currentFileKey]}'");
        }
        $neoName = $neoXML['name']->__toString();
        // Check for existing neography by this name
        $this->currentNeo = Neography::where('machine_name', $neoName)->first();
        if ($this->currentNeo instanceof Neography) {
            // Check if we have more info about it now
            if (!$this->currentNeo->sections()->exists() && isset($neoXML->section)) {
                if (empty($this->currentNeo->name) && isset($neoXML['human'])) {
                    $this->currentNeo->name = $neoXML['human']->__toString();
                }
                if (empty($this->currentNeo->font_svg) && isset($neoXML['svg'])) {
                    if (empty($this->rootPath)) {
                        $fontFile = self::readFontFile(dirname($this->mainFilePaths[$this->currentFileKey]) . "/" . $neoXML['svg']);
                    } else {
                        $fontFile = self::readFontFile(rtrim($this->rootPath,"/") . "/" . $neoXML['svg']);
                    }
                    $this->currentNeo->font_svg = $fontFile;
                }
                if (empty($this->currentNeo->font_ttf) && isset($neoXML['ttf'])) {
                    if (empty($this->rootPath)) {
                        $fontFile = self::readFontFile(dirname($this->mainFilePaths[$this->currentFileKey]) . "/" . $neoXML['ttf']);
                    } else {
                        $fontFile = self::readFontFile(rtrim($this->rootPath,"/") . "/" . $neoXML['ttf']);
                    }
                    $this->currentNeo->font_ttf = $fontFile;
                }
                $this->currentNeo->save();
            }
        } else {
            // If none found, create one
            $this->currentNeo = new Neography();
            $this->currentNeo->sort_keys_job_queued = true; // Pause queued jobs until we're done
            $this->currentNeo->machine_name = $neoName;
            if (isset($neoXML['human'])) {
                $this->currentNeo->name = $neoXML['human']->__toString();
            }
            if (isset($neoXML['svg'])) {
                if (empty($this->rootPath)) {
                    $fontFile = self::readFontFile(dirname($this->mainFilePaths[$this->currentFileKey]) . "/" . $neoXML['svg']);
                } else {
                    $fontFile = self::readFontFile(rtrim($this->rootPath,"/") . "/" . $neoXML['svg']);
                }
                $this->currentNeo->font_svg = $fontFile;
            }
            if (isset($neoXML['ttf'])) {
                if (empty($this->rootPath)) {
                    $fontFile = self::readFontFile(dirname($this->mainFilePaths[$this->currentFileKey]) . "/" . $neoXML['ttf']);
                } else {
                    $fontFile = self::readFontFile(rtrim($this->rootPath,"/") . "/" . $neoXML['ttf']);
                }
                $this->currentNeo->font_ttf = $fontFile;
            }
            $this->currentNeo->save();
        }
        // Save in cache
        $this->validNeos[$this->currentNeo->machine_name] = $this->currentNeo;
        // Parse sections
        if (isset($neoXML->section)) {
            foreach (iterator_to_array($neoXML->section, false) as $position => $neoSectXML) {
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
        // Calculate canonical glyph order
        app(BuildGlyphCanonicalRanks::class)($this->currentNeo);
    }

    /**
     * Parse a <section/> XML element into a NeographySection model
     */
    protected function readNeographySection(
        \SimpleXMLElement $neoSectXML,
        int $position
    ): void
    {
        $neoSectModel = new NeographySection();
        $neoSectModel->neography_id = $this->currentNeo->id;
        if (isset($neoSectXML['type'])) {
            $neoSectModel->type = NeographySectionType::tryFrom($neoSectXML['type']);
        }
        if (isset($neoSectXML['title'])) {
            $neoSectModel->name = $neoSectXML['title']->__toString();
        }
        if (isset($neoSectXML->intro)) {
            $neoSectModel->intro = $this->parseBodyText($neoSectXML->intro);
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
        \SimpleXMLElement $dataXML
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
            if (isset($dataXML->entry)) {
                foreach (iterator_to_array($dataXML->entry, false) as $position => $glyphXML) {
                    $this->readNeographyGlyph(
                        glyphGroupModel: $glyphGroupModel,
                        glyphXML: $glyphXML,
                        position: $position
                    );
                }
            }
        } else {
            /**
             * However if we do NOT have <entry/> elements directly under the <data/> element,
             * that means we could have any number / combo of <symbols/> and <marks/> elements
             * which are explicit glyph groups in the source XML and must be handled thus.
             */
            if (count($dataXML->children()) > 0) {
                foreach (iterator_to_array($dataXML->children(), false) as $groupPosition => $glyphGroupXML) {
                    $glyphGroupModel = new NeographyGlyphGroup();
                    $glyphGroupModel->section_id = $neoSectModel->id;
                    $glyphGroupModel->type = match ($glyphGroupXML->getName()) {
                        'symbols' => NeographyGlyphType::from('symbol'),
                        'marks' => NeographyGlyphType::from('mark'),
                        default => null
                    };
                    $glyphGroupModel->position = $groupPosition;
                    $glyphGroupModel->save();
                    if (isset($glyphGroupXML->entry)) {
                        foreach (iterator_to_array($glyphGroupXML->entry, false) as $position => $glyphXML) {
                            $this->readNeographyGlyph(
                                glyphGroupModel: $glyphGroupModel,
                                glyphXML: $glyphXML,
                                position: $position
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Parse a neography <entry/> XML element into a NeographyGlyph model
     */
    protected function readNeographyGlyph(
        NeographyGlyphGroup $glyphGroupModel,
        \SimpleXMLElement $glyphXML,
        int $position
    ): void
    {
        $glyphModel = new NeographyGlyph();
        if (isset($glyphXML['id'])) {
            $glyphModel->global_id = $glyphXML['id']->__toString();
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
            $glyphModel->transliterated = $glyphXML->roman->__toString();
        }
        if (isset($glyphXML->phonemic)) {
            $glyphModel->phonemic = $glyphXML->phonemic->__toString();
        }
        if (isset($glyphXML->pronunciation->roman)) {
            $glyphModel->pronunciation_transliterated = $glyphXML->pronunciation->roman->__toString();
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
    protected function readWordClassGroup(\SimpleXMLElement $groupXML): void
    {
        $groupModel = new WordClassGroup();
        $groupModel->language_id = $this->currentLang->id;
        $groupModel->save();
        // Read through word classes in this group
        foreach ($groupXML->list->class as $classXML) {
            $this->readWordClass(
                groupModel: $groupModel,
                classXML: $classXML
            );
        }
        // Read through inflection tables in this group
        if (isset($groupXML->layout->table)) {
            /**
             * We have to be careful here, because the source XML schema long
             * predates the separation of InflectionColumns from
             * InflectionTables in the Tollerus data model.
             *
             * See: https://github.com/petermarkley/tollerus/pull/2
             *
             * So we have to infer hierarchy boundaries from the `stack` flag
             * in the XML.
             */
            $chunkedTableCollection = collect(iterator_to_array($groupXML->layout->table, false))
                ->chunkWhile(function ($tableXML, $key, $chunk) {
                    return (
                        filter_var($tableXML['stack'],      FILTER_VALIDATE_BOOLEAN) &&
                        filter_var($chunk->last()['stack'], FILTER_VALIDATE_BOOLEAN)
                    );
                })->all();
            foreach ($chunkedTableCollection as $position => $chunkedTableXML) {
                $this->readInflectionTable(
                    groupModel: $groupModel,
                    chunkedTableXML: $chunkedTableXML->values()->all(),
                    position: $position,
                );
            }
        }
    }

    /**
     * Parse a <class/> XML element in the inflections config into a WordClass model
     */
    protected function readWordClass(
        WordClassGroup $groupModel,
        \SimpleXMLElement $classXML
    ): void
    {
        $classModel = new WordClass();
        $classModel->group_id = $groupModel->id;
        $classModel->language_id = $this->currentLang->id;
        if (!isset($classXML['name']) || empty($classXML['name'])) {
            throw new \RuntimeException("There's a word class with no name in file '{$this->inflectionsFilePath}'");
        }
        $classModel->name = $classXML['name']->__toString();
        $classModel->save();
        /**
         * Since the legacy XML schema had no concept of a "primary class"
         * in a group, we'll set as primary the first one we see.
         */
        if ($groupModel->primary_class == null) {
            $groupModel->primary_class = $classModel->id;
            $groupModel->save();
        }
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
     * Parse the pre-chunked XML data in the inflections config into an InflectionTable model
     */
    protected function readInflectionTable(
        WordClassGroup $groupModel,
        array $chunkedTableXML,
        int $position,
    ): void
    {
        $tableModel = new InflectionTable();
        $tableModel->word_class_group_id = $groupModel->id;
        $tableModel->position = $position;
        /**
         * In typical usage of the legacy table schema, the row-level `fold`
         * attribute of the second column in a stack was more significant
         * than that of the first. The row labels of the first column were
         * never folded in known any scenario, whereas the next column
         * (along with all following columns in the same stack) often was,
         * depending on author preference about that column. Furthermore
         * because all columns from the second onward always matched each
         * other, the second serves as a suitable indicator for the entire
         * table.
         *
         * So in mapping the old config to the new, we choose the second
         * column if present, to represent the entire table for these
         * properties, and only read the first one as a fallback.
         */
        $testTable = $chunkedTableXML[1] ?? $chunkedTableXML[0];
        if (isset($testTable['align_on_stack'])) {
            $tableModel->align_on_stack = filter_var(
                $testTable['align_on_stack'],
                FILTER_VALIDATE_BOOLEAN
            );
        }
        if (isset($testTable->rows['fold'])) {
            $tableModel->rows_fold = filter_var(
                $testTable->rows['fold'],
                FILTER_VALIDATE_BOOLEAN
            );
        }
        /**
         * Here, the first table is the preferred indicator.
         */
        if (isset($chunkedTableXML[0]['fold'])) {
            $tableModel->cols_fold = filter_var(
                $chunkedTableXML[0]['fold'],
                FILTER_VALIDATE_BOOLEAN
            );
        }
        $tableModel->save();
        foreach ($chunkedTableXML as $position => $tableXML) {
            $this->readInflectionColumn(
                groupModel: $groupModel,
                tableModel: $tableModel,
                tableXML: $tableXML,
                position: $position
            );
        }
    }

    /**
     * Parse a <table/> XML element in the inflections config into an InflectionColumn model
     */
    protected function readInflectionColumn(
        WordClassGroup $groupModel,
        InflectionTable $tableModel,
        \SimpleXMLElement $tableXML,
        int $position
    ): void
    {
        $columnModel = new InflectionColumn();
        $columnModel->inflect_table_id = $tableModel->id;
        $columnModel->position = $position;
        if (isset($tableXML['label'])) {
            $columnModel->label = $tableXML['label']->__toString();
        }
        $columnModel->save();
        // Read through filters for this inflection table
        foreach ($tableXML->filter->inflect as $filterXML) {
            $dimension = $filterXML['dimension'];
            if ($dimension == 'verb_role') {
                $dimension = 'role';
            }
            $array = $this->addFeatureIfNew(
                wordClassGroup: $groupModel,
                featureName: $dimension,
                valueName: $filterXML['value']
            );
            [
                'feature' => $featureModel,
                'value' => $valueModel
            ] = $array;
            // Add connection between inflection column and feature values
            $pivot = new InflectionColumnFilter([
                'inflect_column_id' => $columnModel->id,
                'feature_id' => $featureModel->id,
                'value_id' => $valueModel->id,
            ]);
            $pivot->save();
        }
        // Read through rows for this inflection table
        if (isset($tableXML->rows->row)) {
            foreach (iterator_to_array($tableXML->rows->row, false) as $rowPosition => $rowXML) {
                $this->readInflectionRow(
                    groupModel: $groupModel,
                    tableModel: $tableModel,
                    columnModel: $columnModel,
                    rowXML: $rowXML,
                    position: $rowPosition
                );
            }
        }
    }

    /**
     * Parse a <row/> XML element in the inflections config into a InflectionRow model
     */
    protected function readInflectionRow(
        WordClassGroup $groupModel,
        InflectionTable $tableModel,
        InflectionColumn $columnModel,
        \SimpleXMLElement $rowXML,
        int $position
    ): void
    {
        $rowModel = new InflectionRow();
        $rowModel->inflect_column_id = $columnModel->id;
        if (!isset($rowXML['label']) || empty($rowXML['label'])) {
            throw new \RuntimeException("There's a table row with no label in file '{$this->inflectionsFilePath}'");
        }
        $rowModel->label = $rowXML['label']->__toString();
        if (isset($rowXML['brief'])) {
            $rowModel->label_brief = $rowXML['brief']->__toString();
        }
        if (isset($rowXML['long'])) {
            $rowModel->label_long = $rowXML['long']->__toString();
        }
        $rowModel->position = $position;
        $rowModel->save();
        // Read through filters for this table row
        foreach ($rowXML->filter->inflect as $filterXML) {
            $dimension = $filterXML['dimension'];
            if ($dimension == 'verb_role') {
                $dimension = 'role';
            }
            $array = $this->addFeatureIfNew(
                wordClassGroup: $groupModel,
                featureName: $dimension,
                valueName: $filterXML['value']
            );
            [
                'feature' => $featureModel,
                'value' => $valueModel
            ] = $array;
            // Add connection between inflection table and feature values
            $pivot = new InflectionRowFilter([
                'inflect_row_id' => $rowModel->id,
                'feature_id' => $featureModel->id,
                'value_id' => $valueModel->id,
            ]);
            $pivot->save();
        }
    }

    /**
     * Parse a dictionary <entry/> XML element into an Entry model
     */
    protected function readEntry(\SimpleXMLElement $entryXML): void
    {
        $entryModel = new Entry();
        if (isset($entryXML['id'])) {
            $entryModel->global_id = $entryXML['id']->__toString();
        }
        $entryModel->language_id = $this->currentLang->id;
        if (isset($entryXML->etym)) {
            $entryModel->etym = $this->parseBodyText($entryXML->etym);
        }
        $entryModel->save();
        // Read through lexemes
        if (isset($entryXML->class)) {
            foreach (iterator_to_array($entryXML->class, false) as $position => $lexemeXML) {
                $this->readLexeme(
                    entryModel: $entryModel,
                    lexemeXML: $lexemeXML,
                    position: $position
                );
            }
        }
    }

    /**
     * Parse a dictionary <class/> XML element into a Lexeme model
     */
    protected function readLexeme(
        Entry $entryModel,
        \SimpleXMLElement $lexemeXML,
        int $position
    ): void
    {
        $lexemeModel = new Lexeme();
        if (isset($lexemeXML['id'])) {
            $lexemeModel->global_id = $lexemeXML['id']->__toString();
        }
        $lexemeModel->entry_id = $entryModel->id;
        $lexemeModel->language_id = $this->currentLang->id;
        $lexemeModel->position = $position;
        // Check if we have a word class already, if not add one
        if (!isset($lexemeXML['type']) || empty($lexemeXML['type'])) {
            throw new \RuntimeException("There's an entry class with no type in file '{$this->mainFilePaths[$this->currentFileKey]}'");
        }
        if (isset($this->currentClasses[$lexemeXML['type']->__toString()])) {
            // Already in cache, just read from there
            $groupModel = $this->currentClasses[$lexemeXML['type']->__toString()]['group'];
            $classModel = $this->currentClasses[$lexemeXML['type']->__toString()]['class'];
        } else {
            // Not in cache, create group
            $groupModel = new WordClassGroup();
            $groupModel->language_id = $this->currentLang->id;
            $groupModel->save();
            // Now create class
            $classModel = new WordClass();
            $classModel->group_id = $groupModel->id;
            $classModel->language_id = $this->currentLang->id;
            $classModel->name = $lexemeXML['type']->__toString();
            $classModel->save();
            // Save both to cache
            $this->currentClasses[$lexemeXML['type']->__toString()] = [
                'group' => $groupModel,
                'class' => $classModel
            ];
        }
        // Now that we officially have a word class, hook it up
        $lexemeModel->word_class_id = $classModel->id;
        // Save model
        $lexemeModel->save();
        // Read through inflection forms
        if (isset($lexemeXML->morph->form)) {
            foreach ($lexemeXML->morph->form as $formXML) {
                $this->readForm(
                    entryModel: $entryModel,
                    groupModel: $groupModel,
                    lexemeModel: $lexemeModel,
                    formXML: $formXML
                );
            }
        }
        // Read through definition data
        if (isset($lexemeXML->def->sense)) {
            foreach (iterator_to_array($lexemeXML->def->sense, false) as $position => $senseXML) {
                $this->readSense(
                    lexemeModel: $lexemeModel,
                    senseXML: $senseXML,
                    position: $position
                );
            }
        }
    }

    /**
     * Parse a dictionary <form/> XML element into a Form model
     */
    protected function readForm(
        Entry $entryModel,
        WordClassGroup $groupModel,
        Lexeme $lexemeModel,
        \SimpleXMLElement $formXML
    ): void
    {
        $formModel = new Form();
        if (isset($formXML['id'])) {
            $formModel->global_id = $formXML['id']->__toString();
        }
        $formModel->lexeme_id = $lexemeModel->id;
        $formModel->language_id = $this->currentLang->id;
        if (isset($formXML->roman)) {
            $formModel->transliterated = $formXML->roman->__toString();
        }
        if (isset($formXML->phonemic)) {
            $formModel->phonemic = $formXML->phonemic->__toString();
        }
        if (isset($formXML['irregular'])) {
            $formModel->irregular = filter_var($formXML['irregular'], FILTER_VALIDATE_BOOLEAN);
        }
        // Save model
        $formModel->save();
        // Check if this is the primary word form
        if (isset($formXML['primary']) && filter_var($formXML['primary'], FILTER_VALIDATE_BOOLEAN)) {
            $entryModel->primary_form = $formModel->id;
            $entryModel->save();
        }
        // Read through native spellings
        foreach ($formXML->children() as $childXML) {
            $childName = $childXML->getName();
            if ($childName == 'roman' || $childName == 'phonemic') {
                continue;
            }
            $this->readNativeSpelling(
                formModel: $formModel,
                nodeName: $childName,
                nodeXML: $childXML
            );
        }
        // Read through inflection features
        foreach ($formXML->attributes() as $key => $item) {
            if ($key == 'primary' || $key == 'id' || $key == 'irregular') {
                continue;
            }
            $array = $this->addFeatureIfNew(
                wordClassGroup: $groupModel,
                featureName: $key,
                valueName: $item
            );
            [
                'feature' => $featureModel,
                'value' => $valueModel
            ] = $array;
            // Add connection between word form and feature value
            $pivot = new FormFeatureValue([
                'form_id' => $formModel->id,
                'feature_id' => $featureModel->id,
                'value_id' => $valueModel->id,
            ]);
            $pivot->save();
        }
    }

    /**
     * Parse a native spelling XML element (such as '<myneography/>') into a NativeSpelling model
     */
    protected function readNativeSpelling(
        Form $formModel,
        string $nodeName,
        \SimpleXMLElement $nodeXML
    ): void
    {
        if (!isset($this->validNeos[$nodeName])) {
            throw new \RuntimeException("Neography '{$nodeName}' not recognized for this language, in file '{$this->mainFilePaths[$this->currentFileKey]}'");
        }
        $neoModel = $this->validNeos[$nodeName];
        $spellingModel = new NativeSpelling();
        $spellingModel->form_id = $formModel->id;
        $spellingModel->neography_id = $neoModel->id;
        $spellingModel->spelling = $nodeXML->__toString();
        $spellingModel->save();
    }

    /**
     * Parse a dictionary <sense/> XML element into a Sense model
     */
    protected function readSense(
        Lexeme $lexemeModel,
        \SimpleXMLElement $senseXML,
        int $position
    ): void
    {
        $senseModel = new Sense();
        $senseModel->lexeme_id = $lexemeModel->id;
        if (isset($senseXML['num'])) {
            $senseModel->num = $senseXML['num']->__toString();
        } else {
            $senseModel->num = $position;
        }
        $senseModel->body = $this->parseBodyText($senseXML, 'p');
        $senseModel->save();
        // Read through subsenses
        if (isset($senseXML->subsense)) {
            foreach (iterator_to_array($senseXML->subsense, false) as $subPosition => $subsenseXML) {
                $this->readSubsense(
                    senseModel: $senseModel,
                    subsenseXML: $subsenseXML,
                    position: $subPosition
                );
            }
        }
    }

    /**
     * Parse a dictionary <subsense/> XML element into a Subsense model
     */
    protected function readSubsense(
        Sense $senseModel,
        \SimpleXMLElement $subsenseXML,
        int $position
    ): void
    {
        $subsenseModel = new Subsense();
        $subsenseModel->sense_id = $senseModel->id;
        if (isset($subsenseXML['num'])) {
            $subsenseModel->num = $subsenseXML['num']->__toString();
        } else {
            $subsenseModel->num = $position;
        }
        $subsenseModel->body = $this->parseBodyText($subsenseXML, 'p');
        $subsenseModel->save();
    }

    /**
     * Parse body/intro text fields to convert custom tags:
     *
     *   - `<c>`                                 => `<span data-tollerus="smallcaps">`,
     *   - `<word href="AAR3" lang="myconlang">` => `<a href="/tollerus?id=AAR3" data-tollerus="word" data-id="AAR3" data-lang="myconlang">`,
     *   - `<myneography>`                       => `<span data-tollerus="native" data-neography="myneography" class="tollerus_custom_myneography">`,
     *   - `<phonemic>`                          => `<span data-tollerus="phonemic">`,
     *
     * Notes:
     *
     *   - The `<myneography>` tag is just an example. This should be
     *     matched by neography machine_name.
     *
     *   - The URL in `<word> => <a href="">` is a placeholder, to
     *     be overwritten on display. But it should reflect the
     *     host app's `config('tollerus.public_route_prefix')`.
     */
    protected function parseBodyText(\SimpleXMLElement $inpXML, ?string $filterBy = null): string
    {
        $domNode = dom_import_simplexml($inpXML);

        /**
         * Perform substitutions
         */

        // `<c>` => `<span data-tollerus="smallcaps">`
        $tags = iterator_to_array($domNode->getElementsByTagName('c'));
        foreach ($tags as $oldTag) {
            $newTag = $domNode->ownerDocument->createElement('span');
            foreach (iterator_to_array($oldTag->childNodes) as $child) {
                $newTag->appendChild($oldTag->removeChild($child));
            }
            $newTag->setAttribute('data-tollerus', 'smallcaps');
            $oldTag->parentNode->replaceChild($newTag, $oldTag);
        }

        // `<word href="AAR3" lang="myconlang">` => `<a href="/tollerus?id=AAR3" data-tollerus="word" data-id="AAR3" data-lang="myconlang">`,
        $tags = iterator_to_array($domNode->getElementsByTagName('word'));
        foreach ($tags as $oldTag) {
            $newTag = $domNode->ownerDocument->createElement('a');
            foreach (iterator_to_array($oldTag->childNodes) as $child) {
                $newTag->appendChild($oldTag->removeChild($child));
            }
            $id = $oldTag->getAttribute('href');
            $newTag->setAttribute('href', route('tollerus.public.index', ['id' => $id], false)); // Placeholder, to be overwritten at render time by named route created from `data-*` attrs
            $newTag->setAttribute('data-tollerus', 'word');
            $newTag->setAttribute('data-id', $id);
            $newTag->setAttribute('data-lang', $oldTag->getAttribute('lang'));
            $oldTag->parentNode->replaceChild($newTag, $oldTag);
        }

        // `<myneography>` => `<span data-tollerus="native" data-neography="myneography" class="tollerus_custom_myneography">`,
        foreach (array_keys($this->validNeos) as $neoMachineName) {
            $tags = iterator_to_array($domNode->getElementsByTagName($neoMachineName));
            foreach ($tags as $oldTag) {
                $newTag = $domNode->ownerDocument->createElement('span');
                foreach (iterator_to_array($oldTag->childNodes) as $child) {
                    $newTag->appendChild($oldTag->removeChild($child));
                }
                $newTag->setAttribute('data-tollerus', 'native');
                $newTag->setAttribute('data-neography', $neoMachineName);
                $newTag->setAttribute('class', 'tollerus_custom_' . $neoMachineName);
                $oldTag->parentNode->replaceChild($newTag, $oldTag);
            }
        }

        // `<phonemic>` => `<span data-tollerus="phonemic">`,
        $tags = iterator_to_array($domNode->getElementsByTagName('phonemic'));
        foreach ($tags as $oldTag) {
            $newTag = $domNode->ownerDocument->createElement('span');
            foreach (iterator_to_array($oldTag->childNodes) as $child) {
                $newTag->appendChild($oldTag->removeChild($child));
            }
            $newTag->setAttribute('data-tollerus', 'phonemic');
            $oldTag->parentNode->replaceChild($newTag, $oldTag);
        }

        /**
         * Omit root tag on output
         */
        return collect($domNode->childNodes)
            ->map(function ($item) use ($domNode, $filterBy) {
                if (empty($filterBy) || ($item->nodeType === XML_ELEMENT_NODE && $item->nodeName === $filterBy)) {
                    return $domNode->ownerDocument->saveXML($item);
                } else {
                    return '';
                }
            })
            ->implode('');
    }
}
