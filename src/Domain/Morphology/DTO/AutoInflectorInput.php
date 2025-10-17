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
        public array $baseRegExs,
        public array $particleRegExs,
        public string $template,
    ) {}

    /**
     * This DTO for the AutoInflector is expected to be used mainly
     * as a UI aid when inputting new entries in the dictionary. That
     * means that the actual base string(s) to be inflected may not
     * exist yet in the database anywhere, and must be passed in from
     * the calling context.
     *
     * A situation where it does pre-exist in the database implies a
     * partial/incomplete word entry, which is not supposed to happen
     * normally.
     *
     * Each `MorphRule` object represents one call to preg_replace().
     * The input for these calls can be either a base or particle,
     * and either the roman, phonemic, or native spelling of it.
     *
     * Each call to this DTO class collects the morph rules for one
     * base-particle pair. A single word form is expected to call it
     * 3 or more times, varying MorphRulePatternType and/or
     * neographyId each time.
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
        $baseRegExs = $row->morphRules
            ->filter(fn($t) =>
                $t->pattern_type == $type &&
                $t->target_type == MorphRuleTargetType::BaseInput
            )->sortBy('order')
            ->map->only(['pattern', 'replacement'])
            ->toArray();
        $particleRegExs = $row->morphRules
            ->filter(fn($t) =>
                $t->pattern_type == $type &&
                $t->target_type == MorphRuleTargetType::ParticleInput
            )->sortBy('order')
            ->map->only(['pattern', 'replacement'])
            ->toArray();
        return new self(
            base: $base,
            particle: $particleString,
            baseRegExs: $baseRegExs,
            particleRegExs: $particleRegExs,
            template: $row->morph_template,
        );
    }
}