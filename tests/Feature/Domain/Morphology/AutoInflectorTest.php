<?php

use PeterMarkley\Tollerus\Database\Seeders\FileImportSeeder;
use PeterMarkley\Tollerus\Domain\Morphology\DTO\AutoInflectorInput;
use PeterMarkley\Tollerus\Domain\Morphology\Services\AutoInflector;
use PeterMarkley\Tollerus\Enums\MorphRulePatternType;
use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\InflectionRow;
use PeterMarkley\Tollerus\Models\Neography;

it('correctly auto-inflects a transliteration', function () {
    // Set up database context
    (new FileImportSeeder())->run();
    $particle = Form::where('transliterated', 'wampoo')->firstOrFail();
    $row = InflectionRow::firstOrFail();
    $row->src_particle = $particle->id;
    $row->morph_template = '{B}{P}';
    $row->save();
    $row->morphRules()->create([
        'pattern' => '..$',
        'target_type' => MorphRuleTargetType::BaseInput,
        'pattern_type' => MorphRulePatternType::Transliterated,
        'order' => 0,
    ]);
    $row->morphRules()->create([
        'pattern' => '^.{4}',
        'target_type' => MorphRuleTargetType::ParticleInput,
        'pattern_type' => MorphRulePatternType::Transliterated,
        'order' => 0,
    ]);

    // Test DTO
    $baseStr = 'farbar';
    $autoInflectorData = AutoInflectorInput::fromRow($row, $baseStr, MorphRulePatternType::Transliterated);
    expect($autoInflectorData)->toBeInstanceOf(AutoInflectorInput::class);

    // Test service
    $output = (new AutoInflector($row, $baseStr, MorphRulePatternType::Transliterated))->inflect();
    expect($output)->toBe('farboo');
});

it('correctly auto-inflects a phonemic transcription', function () {
    // Set up database context
    (new FileImportSeeder())->run();
    $particle = Form::where('transliterated', 'wampoo')->firstOrFail();
    $row = InflectionRow::firstOrFail();
    $row->src_particle = $particle->id;
    $row->morph_template = '{B}{P}';
    $row->save();
    $row->morphRules()->create([
        'pattern' => '..$',
        'target_type' => MorphRuleTargetType::BaseInput,
        'pattern_type' => MorphRulePatternType::Phonemic,
        'order' => 0,
    ]);
    $row->morphRules()->create([
        'pattern' => '^.{6}',
        'target_type' => MorphRuleTargetType::ParticleInput,
        'pattern_type' => MorphRulePatternType::Phonemic,
        'order' => 0,
    ]);

    // Test DTO
    $baseStr = 'ˈfɑɹˌbɑɹ';
    $autoInflectorData = AutoInflectorInput::fromRow($row, $baseStr, MorphRulePatternType::Phonemic);
    expect($autoInflectorData)->toBeInstanceOf(AutoInflectorInput::class);

    // Test service
    $output = (new AutoInflector($row, $baseStr, MorphRulePatternType::Phonemic))->inflect();
    expect($output)->toBe('ˈfɑɹˌbu');
});

it('correctly auto-inflects a native spelling', function () {
    // Set up database context
    (new FileImportSeeder())->run();
    $neography = Neography::where('machine_name', 'myneography')->firstOrFail();
    $particle = Form::where('transliterated', 'wampoo')->firstOrFail();
    $row = InflectionRow::firstOrFail();
    $row->src_particle = $particle->id;
    $row->morph_template = '{B}{P}';
    $row->save();
    $row->morphRules()->create([
        'pattern' => '..$',
        'neography_id' => $neography->id,
        'target_type' => MorphRuleTargetType::BaseInput,
        'pattern_type' => MorphRulePatternType::Native,
        'order' => 0,
    ]);
    $row->morphRules()->create([
        'pattern' => '^.{4}',
        'neography_id' => $neography->id,
        'target_type' => MorphRuleTargetType::ParticleInput,
        'pattern_type' => MorphRulePatternType::Native,
        'order' => 0,
    ]);

    // Test DTO
    $baseStr = 'farbar';
    $autoInflectorData = AutoInflectorInput::fromRow($row, $baseStr, MorphRulePatternType::Native, $neography->id);
    expect($autoInflectorData)->toBeInstanceOf(AutoInflectorInput::class);

    // Test service
    $output = (new AutoInflector($row, $baseStr, MorphRulePatternType::Native, $neography->id))->inflect();
    expect($output)->toBe('farb');
});
