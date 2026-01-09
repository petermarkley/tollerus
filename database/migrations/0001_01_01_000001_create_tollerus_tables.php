<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PeterMarkley\Tollerus\Enums\GlobalIdKind;
use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Enums\NeographySectionType;
use PeterMarkley\Tollerus\Enums\NeographyGlyphType;
use PeterMarkley\Tollerus\Enums\PatternType;
use PeterMarkley\Tollerus\Enums\WritingDirection;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = Schema::connection(config('tollerus.connection'));
        $rawConnection = DB::connection(config('tollerus.connection'));
        $prefix = $rawConnection->getTablePrefix();
        
        /**
         * ===========================================================
         *                 TOP-LEVEL LANGUAGE CONFIG
         * These tables are expected to be mostly set-and-forget, i.e.
         * they're not expected to change much after initial setup.
         * ===========================================================
         */
        
        $connection->create('neographies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('machine_name')->unique();
            $table->binary('font_svg', length: config('tollerus.max_font_size'))->nullable();
            $table->binary('font_ttf', length: config('tollerus.max_font_size'))->nullable();
            $table->string('font_svg_file_path')->nullable();
            $table->string('font_svg_url')->nullable();
            $table->string('font_ttf_file_path')->nullable();
            $table->string('font_ttf_url')->nullable();
            $table->enum('direction_primary', WritingDirection::values())
                ->default(WritingDirection::LeftToRight->value);
            // This should always be perpendicular to the primary direction
            $table->enum('direction_secondary', WritingDirection::values())
                ->default(WritingDirection::TopToBottom->value);
            /**
             * Boustrophedon means "as the ox plows," meaning the primary
             * direction flips each time the line is filled and the script
             * increments in the secondary direction.
             */
            $table->boolean('boustrophedon')->default(false);
            $table->boolean('visible')
                ->default(true);
            $table->boolean('sort_keys_job_queued')->default(false);
        });
        
        $connection->create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('machine_name')->unique();
            $table->string('dict_title')->nullable();
            $table->string('dict_title_full')->nullable();
            $table->string('dict_author')->nullable();
            $table->foreignId('primary_neography')->nullable();
            $table->foreign('primary_neography')
                ->references('id')->on('neographies')
                ->nullOnDelete();
            $table->text('intro')->charset('utf8mb4')->nullable();
            $table->boolean('visible')
                ->default(true);
        });

        $connection->create('language_neography', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id');
            $table->foreign('language_id')
                ->references('id')->on('languages')
                ->cascadeOnDelete();
            $table->foreignId('neography_id');
            $table->foreign('neography_id')
                ->references('id')->on('neographies')
                ->cascadeOnDelete();
            // ensure only one pivot row for each combo
            $table->unique(['language_id', 'neography_id'], 'language_neography_unique');
        });

        $connection->create('word_class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id');
            $table->foreign('language_id')
                ->references('id')->on('languages')
                ->cascadeOnDelete();
            $table->foreignId('primary_class')->nullable(); // Relationship defined after `word_classes` table
            $table->boolean('inflected')->default(false); // FIXME not sure if we need this, can be derived
        });

        $connection->create('word_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id');
            $table->foreign('language_id')
                ->references('id')->on('languages')
                ->cascadeOnDelete();
            $table->foreignId('group_id');
            $table->foreign('group_id')
                ->references('id')->on('word_class_groups')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('name_brief')->nullable();
            // ensure only one of each word class name per language
            $table->unique(['language_id', 'name'], 'language_name_unique');
            $table->unique(['language_id', 'name_brief'], 'language_name_brief_unique');
        });
        $connection->table('word_class_groups', function (Blueprint $table) {
            $table->foreign('primary_class')
                ->references('id')->on('word_classes')
                ->nullOnDelete();
        });

        $connection->create('features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_class_group_id');
            $table->foreign('word_class_group_id')
                ->references('id')->on('word_class_groups')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('name_brief')->nullable();
            // ensure only one of each feature name per word class group
            $table->unique(['word_class_group_id', 'name'], 'group_name_unique');
            $table->unique(['word_class_group_id', 'name_brief'], 'group_name_brief_unique');
        });

        $connection->create('feature_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id');
            $table->foreign('feature_id')
                ->references('id')->on('features')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('name_brief')->nullable();
            // ensure only one of each value name per feature
            $table->unique(['feature_id', 'name'], 'feature_name_unique');
            $table->unique(['feature_id', 'name_brief'], 'feature_name_brief_unique');
        });

        $connection->create('neography_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('neography_id');
            $table->foreign('neography_id')
                ->references('id')->on('neographies')
                ->cascadeOnDelete();
            $table->enum('type', NeographySectionType::values())->nullable();
            $table->string('name');
            $table->text('intro')->charset('utf8mb4');
            $table->integer('position');
            // ensure only one of each section name per neography
            $table->unique(['neography_id', 'name'], 'neography_name_unique');
            // ensure only one of each position per neography
            $table->unique(['neography_id', 'position'], 'neography_position_unique');
        });

        $connection->create('neography_glyph_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id');
            $table->foreign('section_id')
                ->references('id')->on('neography_sections')
                ->cascadeOnDelete();
            $table->enum('type', NeographyGlyphType::values())->nullable();
            $table->integer('position');
            // ensure only one of each position per section
            $table->unique(['section_id', 'position'], 'section_position_unique');
        });

        $connection->create('neography_input_keyboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('neography_id');
            $table->foreign('neography_id')
                ->references('id')->on('neographies')
                ->cascadeOnDelete();
            $table->integer('position');
            $table->integer('width');
            // ensure only one of each position per neography
            $table->unique(['neography_id', 'position'], 'neography_position_unique');
        });

        $connection->create('neography_input_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyboard_id');
            $table->foreign('keyboard_id')
                ->references('id')->on('neography_input_keyboards')
                ->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('glyph')->charset('utf8mb4')->nullable();
            $table->integer('position');
            $table->boolean('render_base'); // If true, glyph will render on a Unicode dotted circle
            // ensure only one of each glyph per keyboard
            $table->unique(['keyboard_id', 'glyph'], 'keyboard_glyph_unique');
            // ensure only one of each position per keyboard
            $table->unique(['keyboard_id', 'position'], 'keyboard_position_unique');
        });

        /**
         * ===========================================================
         *                 MAIN LEXICAL DATA
         * These tables are where the main body of the dictionary data
         * is stored, expected to be growing and evolving a lot.
         * ===========================================================
         */

        /**
         * This table stores globally unique, canonical IDs
         * that will be exposed to the user in base64
         */
        $connection->create('global_ids', function (Blueprint $table) {
            $table->id('global_id_raw');
            $table->enum('kind', GlobalIdKind::values())
                ->nullable(false);
        });
        $global_ids = $prefix . 'global_ids';

        $connection->create('neography_glyphs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('global_id_raw')->unique();
            $table->foreign('global_id_raw')
                ->references('global_id_raw')->on('global_ids')
                ->cascadeOnDelete();
            $table->foreignId('neography_id');
            $table->foreign('neography_id')
                ->references('id')->on('neographies')
                ->cascadeOnDelete();
            $table->foreignId('group_id');
            $table->foreign('group_id')
                ->references('id')->on('neography_glyph_groups')
                ->cascadeOnDelete();
            $table->integer('position');
            $table->boolean('render_base'); // If true, glyph will render on a Unicode dotted circle
            $table->string('glyph')
                ->charset('utf8mb4')
                ->collation('utf8mb4_bin') // This is needed for the unique constraint below.
                ->nullable();
            $table->string('transliterated')->nullable();
            $table->string('phonemic')->charset('utf8mb4')->nullable();
            $table->string('pronunciation_transliterated')->nullable();
            $table->string('pronunciation_phonemic')->charset('utf8mb4')->nullable();
            $table->string('pronunciation_native')->charset('utf8mb4')->nullable();
            $table->string('note')->charset('utf8mb4')->nullable();
            // special column for canonical glyph order per neography
            $table->unsignedInteger('canonical_rank')->nullable();
            $table->unique(['neography_id', 'canonical_rank'], 'neography_canonical_rank_unique');
            $table->index(['neography_id', 'canonical_rank'], 'neography_canonical_rank_idx');
            // ensure only one of each glyph per group
            $table->unique(['group_id', 'glyph'], 'group_glyph_unique');
            // ensure only one of each position per group
            $table->unique(['group_id', 'position'], 'group_position_unique');
        });
        /**
         * We need a database trigger to help maintain our global IDs.
         */
        $neography_glyphs = $prefix . 'neography_glyphs';
        $kind = GlobalIdKind::Glyph->value;
        $rawConnection->unprepared(<<<SQL
        CREATE TRIGGER bi_{$prefix}neography_glyphs_assign_global_id
        BEFORE INSERT ON {$neography_glyphs} FOR EACH ROW
        BEGIN
          IF NEW.global_id_raw IS NULL THEN
            -- No id dictated: allocate one in global_ids and copy it to the row
            INSERT INTO {$global_ids} (kind) VALUES('{$kind}');
            SET NEW.global_id_raw = LAST_INSERT_ID();
          ELSE
            -- Allow explicit ID; ensure a registry row exists (will fail if taken)
            INSERT INTO {$global_ids} (global_id_raw, kind) VALUES (NEW.global_id_raw, '{$kind}');
          END IF;
        END;
        SQL);
        $rawConnection->unprepared(<<<SQL
        CREATE TRIGGER ad_{$prefix}neography_glyphs_delete_gid
        AFTER DELETE ON {$neography_glyphs} FOR EACH ROW
        BEGIN
          DELETE FROM {$global_ids} WHERE global_id_raw = OLD.global_id_raw;
        END;
        SQL);

        $connection->create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('global_id_raw')->unique();
            $table->foreign('global_id_raw')
                ->references('global_id_raw')->on('global_ids')
                ->cascadeOnDelete();
            $table->foreignId('language_id');
            $table->foreign('language_id')
                ->references('id')->on('languages')
                ->cascadeOnDelete();
            $table->foreignId('primary_form')->nullable(); // Relationship defined after `forms` table
            $table->text('etym')->charset('utf8mb4')->nullable();
        });
        /**
         * We need a database trigger to help maintain our global IDs.
         */
        $entries = $prefix . 'entries';
        $kind = GlobalIdKind::Entry->value;
        $rawConnection->unprepared(<<<SQL
        CREATE TRIGGER bi_{$prefix}entries_assign_global_id
        BEFORE INSERT ON {$entries} FOR EACH ROW
        BEGIN
          IF NEW.global_id_raw IS NULL THEN
            -- No id dictated: allocate one in global_ids and copy it to the row
            INSERT INTO {$global_ids} (kind) VALUES('{$kind}');
            SET NEW.global_id_raw = LAST_INSERT_ID();
          ELSE
            -- Allow explicit ID; ensure a registry row exists (will fail if taken)
            INSERT INTO {$global_ids} (global_id_raw, kind) VALUES (NEW.global_id_raw, '{$kind}');
          END IF;
        END;
        SQL);
        $rawConnection->unprepared(<<<SQL
        CREATE TRIGGER ad_{$prefix}entries_delete_gid
        AFTER DELETE ON {$entries} FOR EACH ROW
        BEGIN
          DELETE FROM {$global_ids} WHERE global_id_raw = OLD.global_id_raw;
        END;
        SQL);

        $connection->create('lexemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('global_id_raw')->unique();
            $table->foreign('global_id_raw')
                ->references('global_id_raw')->on('global_ids')
                ->cascadeOnDelete();
            $table->foreignId('language_id');
            $table->foreign('language_id')
                ->references('id')->on('languages')
                ->cascadeOnDelete();
            $table->foreignId('entry_id');
            $table->foreign('entry_id')
                ->references('id')->on('entries')
                ->cascadeOnDelete();
            $table->foreignId('word_class_id');
            $table->foreign('word_class_id')
                ->references('id')->on('word_classes')
                ->cascadeOnDelete();
            $table->integer('position');
            // ensure only one of each position per entry
            $table->unique(['entry_id', 'position'], 'entry_position_unique');
        });
        /**
         * We need a database trigger to help maintain our global IDs.
         */
        $lexemes = $prefix . 'lexemes';
        $kind = GlobalIdKind::Lexeme->value;
        $rawConnection->unprepared(<<<SQL
        CREATE TRIGGER bi_{$prefix}lexemes_assign_global_id
        BEFORE INSERT ON {$lexemes} FOR EACH ROW
        BEGIN
          IF NEW.global_id_raw IS NULL THEN
            -- No id dictated: allocate one in global_ids and copy it to the row
            INSERT INTO {$global_ids} (kind) VALUES('{$kind}');
            SET NEW.global_id_raw = LAST_INSERT_ID();
          ELSE
            -- Allow explicit ID; ensure a registry row exists (will fail if taken)
            INSERT INTO {$global_ids} (global_id_raw, kind) VALUES (NEW.global_id_raw, '{$kind}');
          END IF;
        END;
        SQL);
        $rawConnection->unprepared(<<<SQL
        CREATE TRIGGER ad_{$prefix}lexemes_delete_gid
        AFTER DELETE ON {$lexemes} FOR EACH ROW
        BEGIN
          DELETE FROM {$global_ids} WHERE global_id_raw = OLD.global_id_raw;
        END;
        SQL);

        $connection->create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('global_id_raw')->unique();
            $table->foreign('global_id_raw')
                ->references('global_id_raw')->on('global_ids')
                ->cascadeOnDelete();
            $table->foreignId('language_id');
            $table->foreign('language_id')
                ->references('id')->on('languages')
                ->cascadeOnDelete();
            $table->foreignId('lexeme_id');
            $table->foreign('lexeme_id')
                ->references('id')->on('lexemes')
                ->cascadeOnDelete();
            $table->string('transliterated');
            $table->string('phonemic')->charset('utf8mb4');
            $table->boolean('irregular')->default(false);
        });
        /**
         * We need a database trigger to help maintain our global IDs.
         */
        $forms = $prefix . 'forms';
        $kind = GlobalIdKind::Form->value;
        $rawConnection->unprepared(<<<SQL
        CREATE TRIGGER bi_{$prefix}forms_assign_global_id
        BEFORE INSERT ON {$forms} FOR EACH ROW
        BEGIN
          IF NEW.global_id_raw IS NULL THEN
            -- No id dictated: allocate one in global_ids and copy it to the row
            INSERT INTO {$global_ids} (kind) VALUES('{$kind}');
            SET NEW.global_id_raw = LAST_INSERT_ID();
          ELSE
            -- Allow explicit ID; ensure a registry row exists (will fail if taken)
            INSERT INTO {$global_ids} (global_id_raw, kind) VALUES (NEW.global_id_raw, '{$kind}');
          END IF;
        END;
        SQL);
        $rawConnection->unprepared(<<<SQL
        CREATE TRIGGER ad_{$prefix}forms_delete_gid
        AFTER DELETE ON {$forms} FOR EACH ROW
        BEGIN
          DELETE FROM {$global_ids} WHERE global_id_raw = OLD.global_id_raw;
        END;
        SQL);

        /**
         * Modify the existing `entries` table to add the
         * foreign key relationship to `forms`.
         */
        $connection->table('entries', function (Blueprint $table) {
            $table->foreign('primary_form')
                ->references('id')->on('forms')
                ->nullOnDelete(); // <- Because during data input, there might be no forms at first
        });

        $connection->create('senses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lexeme_id');
            $table->foreign('lexeme_id')
                ->references('id')->on('lexemes')
                ->cascadeOnDelete();
            $table->integer('num');
            $table->text('body')->charset('utf8mb4');
            // ensure only one of each num per sense
            $table->unique(['lexeme_id', 'num'], 'lexeme_num_unique');
        });

        $connection->create('subsenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sense_id');
            $table->foreign('sense_id')
                ->references('id')->on('senses')
                ->cascadeOnDelete();
            $table->integer('num');
            $table->text('body')->charset('utf8mb4');
            // ensure only one of each num per sense
            $table->unique(['sense_id', 'num'], 'num_sense_unique');
        });

        $connection->create('native_spellings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id');
            $table->foreign('form_id')
                ->references('id')->on('forms')
                ->cascadeOnDelete();
            $table->foreignId('neography_id');
            $table->foreign('neography_id')
                ->references('id')->on('neographies')
                ->cascadeOnDelete();
            $table->string('spelling')->charset('utf8mb4');
            // special column for native sorting
            $table->string('sort_key', 2048)
                ->charset('ascii')
                ->collation('ascii_bin')
                ->nullable();
            $table->index(['neography_id', 'sort_key'], 'native_spellings_neography_sort_key_idx');
            // ensure only one of each native spelling per `form`
            $table->unique(['form_id', 'neography_id'], 'form_neography_unique');
        });

        $connection->create('form_feature_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id');
            $table->foreign('form_id')
                ->references('id')->on('forms')
                ->cascadeOnDelete();
            $table->foreignId('feature_id');
            $table->foreign('feature_id')
                ->references('id')->on('features')
                ->cascadeOnDelete();
            $table->foreignId('value_id');
            $table->foreign('value_id')
                ->references('id')->on('feature_values')
                ->cascadeOnDelete();
            // ensure only one of each feature per `form`
            $table->unique(['form_id', 'feature_id'], 'form_feature_unique');
        });

        /**
         * ===========================================================
         *                 INFLECTION TABLES CONFIG
         * These DB tables define how inflection tables are displayed.
         * Shouldn't change much after initial setup unless you alter
         * or augment the fundamental grammar of your conlang.
         * ===========================================================
         */

        $connection->create('inflect_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_class_group_id');
            $table->foreign('word_class_group_id')
                ->references('id')->on('word_class_groups')
                ->cascadeOnDelete();
            $table->string('label');
            $table->integer('position');
            $table->boolean('visible')->default(true);
            $table->boolean('show_label')->default(true);
            /**
             * A 'stack' value of true means that on wide displays,
             * this table is permitted to have other tables beside it,
             * sharing the vertical space.
             */
            $table->boolean('stack');
            /**
             * If 'align_on_stack' is true, the table's label will
             * align left when the table is stacked horizontally.
             * (It's centered otherwise.)
             */
            $table->boolean('align_on_stack');
            /**
             * Here, true means the table's label is hidden when the
             * table is NOT stacked horizontally (to avoid redundancy
             * if it's the same as the label for the table directly
             * above it).
             */
            $table->boolean('table_fold');
            /**
             * Here, true means the row labels are hidden when the
             * table IS stacked horizontally (to avoid redundancy if
             * it's the same as the label for the row directly across
             * from it).
             */
            $table->boolean('rows_fold');
            // ensure only one of each label per word class group
            $table->unique(['word_class_group_id', 'label'], 'group_label_unique');
            // ensure only one of each position per word class group
            $table->unique(['word_class_group_id', 'position'], 'group_position_unique');
        });

        $connection->create('inflect_table_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inflect_table_id');
            $table->foreign('inflect_table_id')
                ->references('id')->on('inflect_tables')
                ->cascadeOnDelete();
            $table->foreignId('feature_id');
            $table->foreign('feature_id')
                ->references('id')->on('features')
                ->cascadeOnDelete();
            $table->foreignId('value_id');
            $table->foreign('value_id')
                ->references('id')->on('feature_values')
                ->cascadeOnDelete();
            // ensure only one of each feature per inflection table
            $table->unique(['inflect_table_id', 'feature_id'], 'inflect_table_feature_unique');
            // ensure only one of each value per feature
            $table->unique(['inflect_table_id', 'feature_id', 'value_id'], 'inflect_table_feature_value_unique');
        });

        $connection->create('inflect_table_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inflect_table_id');
            $table->foreign('inflect_table_id')
                ->references('id')->on('inflect_tables')
                ->cascadeOnDelete();
            $table->string('label');
            $table->string('label_brief')->nullable();
            $table->string('label_long')->nullable();
            $table->integer('position');
            $table->boolean('visible')->default(true);
            $table->boolean('show_label')->default(true);
            $table->foreignId('src_particle')->nullable();
            $table->foreign('src_particle')
                ->references('id')->on('forms')
                ->nullOnDelete();
            $table->foreignId('src_base')->nullable();
            $table->foreign('src_base')
                ->references('id')->on('inflect_table_rows')
                ->nullOnDelete();
            $table->string('morph_template')->nullable()
                ->default('{B}{P}');
            // ensure only one of each label per inflection table
            $table->unique(['inflect_table_id', 'label'], 'inflect_table_label_unique');
            $table->unique(['inflect_table_id', 'label_brief'], 'inflect_table_label_brief_unique');
            $table->unique(['inflect_table_id', 'label_long'], 'inflect_table_label_long_unique');
            // ensure only one of each position per inflection table
            $table->unique(['inflect_table_id', 'position'], 'inflect_table_position_unique');
        });

        $connection->create('inflect_table_row_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inflect_table_row_id');
            $table->foreign('inflect_table_row_id')
                ->references('id')->on('inflect_table_rows')
                ->cascadeOnDelete();
            $table->foreignId('feature_id');
            $table->foreign('feature_id')
                ->references('id')->on('features')
                ->cascadeOnDelete();
            $table->foreignId('value_id');
            $table->foreign('value_id')
                ->references('id')->on('feature_values')
                ->cascadeOnDelete();
            // ensure only one of each feature per inflection table
            $table->unique(['inflect_table_row_id', 'feature_id'], 'row_feature_unique');
            // ensure only one of each value per feature
            $table->unique(['inflect_table_row_id', 'feature_id', 'value_id'], 'row_feature_value_unique');
        });

        $connection->create('morph_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inflect_table_row_id');
            $table->foreign('inflect_table_row_id')
                ->references('id')->on('inflect_table_rows')
                ->cascadeOnDelete();
            /**
             * Each morph rule is a call to preg_replace(). These values will
             * be directly passed as arguments.
             */
            $table->string('pattern')->charset('utf8mb4');
            $table->string('replacement')->charset('utf8mb4');
            /**
             * These values define where to find the `subject` argument for
             * preg_replace().
             */
            $table->foreignId('neography_id')->nullable();
            $table->foreign('neography_id')
                ->references('id')->on('neographies')
                ->cascadeOnDelete();
            $table->enum('target_type', MorphRuleTargetType::values());
            $table->enum('pattern_type', PatternType::values());
            /**
             * This defines the order in which the calls to preg_replace()
             * should happen on a given inflection row.
             */
            $table->integer('order');
            /**
             * Ensure only one of each order per input for the inflection row.
             *
             * For each inflection row we have 4+(2*N) possible inputs:
             * - transliterated, base
             * - phonemic, base
             * - N number of native bases
             * - transliterated, particle
             * - phonemic, particle
             * - N number of native particles
             *
             * Which one of these inputs we're mutating for the inflection row
             * depends on the values of these 3 fields:
             * - target_type (base|comb)
             * - pattern_type (transliterated|phonemic|native)
             * - neography_id
             *
             * To avoid a 5-way unique constraint, we will merge these 3 fields
             * into one computed column to use instead:
             */
            $table->string('input_slot')
                ->storedAs("CONCAT(target_type,'|',pattern_type,'|',COALESCE(CAST(neography_id AS CHAR),'0'))");
            $table->unique(['inflect_table_row_id', 'input_slot', 'order'], 'row_input_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::connection(config('tollerus.connection'));
        $rawConnection = DB::connection(config('tollerus.connection'));
        $prefix = $rawConnection->getTablePrefix();
        $connection->disableForeignKeyConstraints();

        // triggers
        $rawConnection->unprepared("DROP TRIGGER IF EXISTS bi_{$prefix}neography_glyphs_assign_global_id;");
        $rawConnection->unprepared("DROP TRIGGER IF EXISTS ad_{$prefix}neography_glyphs_delete_gid;");
        $rawConnection->unprepared("DROP TRIGGER IF EXISTS bi_{$prefix}entries_assign_global_id;");
        $rawConnection->unprepared("DROP TRIGGER IF EXISTS ad_{$prefix}entries_delete_gid;");
        $rawConnection->unprepared("DROP TRIGGER IF EXISTS bi_{$prefix}lexemes_assign_global_id;");
        $rawConnection->unprepared("DROP TRIGGER IF EXISTS ad_{$prefix}lexemes_delete_gid;");
        $rawConnection->unprepared("DROP TRIGGER IF EXISTS bi_{$prefix}forms_assign_global_id;");
        $rawConnection->unprepared("DROP TRIGGER IF EXISTS ad_{$prefix}forms_delete_gid;");
        // inflection tables config
        $connection->dropIfExists('morph_rules');
        $connection->dropIfExists('inflect_table_row_filters');
        $connection->dropIfExists('inflect_table_rows');
        $connection->dropIfExists('inflect_table_filters');
        $connection->dropIfExists('inflect_tables');
        // main lexical data
        $connection->dropIfExists('form_feature_values');
        $connection->dropIfExists('native_spellings');
        $connection->dropIfExists('subsenses');
        $connection->dropIfExists('senses');
        $connection->dropIfExists('forms');
        $connection->dropIfExists('lexemes');
        $connection->dropIfExists('entries');
        $connection->dropIfExists('neography_glyphs');
        $connection->dropIfExists('global_ids');
        // top-level language config
        $connection->dropIfExists('neography_input_keys');
        $connection->dropIfExists('neography_input_keyboards');
        $connection->dropIfExists('neography_glyph_groups');
        $connection->dropIfExists('neography_sections');
        $connection->dropIfExists('feature_values');
        $connection->dropIfExists('features');
        $connection->dropIfExists('word_classes');
        $connection->dropIfExists('word_class_groups');
        $connection->dropIfExists('language_neography');
        $connection->dropIfExists('languages');
        $connection->dropIfExists('neographies');

        $connection->enableForeignKeyConstraints();
    }
};
