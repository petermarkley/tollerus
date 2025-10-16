<?php

namespace PeterMarkley\Tollerus\Domain\Morphology\DTO;

use PeterMarkley\Tollerus\Enums\MorphRulePatternType;
use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Models\InflectionTableRow;

final class AutoInflectorInput
{
    public function __construct(
        public string $base,
        public string $particle,
        public array $basePatterns,
        public array $particlePatterns,
        public string $template,
    ) {}

    /**
     * This DTO for the AutoInflector is expected to be used mainly
     * as a UI aid when inputting new entries in the dictionary. That
     * means that the actual base string(s) to be inflected may not
     * exist yet in the database anywhere, and must be passed in from
     * the calling context.
     * 
     * A situation where it does pre-exist in the DB implies a
     * partial/incomplete word entry, which is not supposed to happen
     * normally.
     * 
     * Each auto-inflected word form is expected to need 3 or more
     * calls to this DTO class: 1 each for the roman, phonemic, and
     * native representations of the word form.
     */
    public static function fromRow(
        InflectionTableRow $row,
        string $base,
        MorphRulePatternType $type,
        /**
         * This is only used if $type = MorphRulePatternType::Native
         */
        int $neographyId = null,
    ): self
    {
        $row->loadMissing([
            'sourceParticle.nativeSpellings',
            'morphRules',
        ]);
        $form = $row->sourceParticle;
        $particleString = match($type) {
            MorphRulePatternType::Roman => $form->roman,
            MorphRulePatternType::Phonemic => $form->phonemic,
            MorphRulePatternType::Native => $form
                ->nativeSpellings
                ->first(fn($t)=>$t->neography_id==$neographyId)
                ->spelling,
        };
        $basePatterns = $row->morphRules
            ->filter(fn($t) =>
                $t->pattern_type == $type &&
                $t->target_type == MorphRuleTargetType::BaseInput
            )->sortBy('order')
            ->pluck('pattern')
            ->toArray();
        $particlePatterns = $row->morphRules
            ->filter(fn($t) =>
                $t->pattern_type == $type &&
                $t->target_type == MorphRuleTargetType::ParticleInput
            )->sortBy('order')
            ->pluck('pattern')
            ->toArray();
        return new self(
            base: $base,
            particle: $particleString,
            basePatterns: $basePatterns,
            particlePatterns: $particlePatterns,
            template: $row->morph_template,
        );
    }
}