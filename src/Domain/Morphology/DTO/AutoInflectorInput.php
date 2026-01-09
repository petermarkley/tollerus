<?php

namespace PeterMarkley\Tollerus\Domain\Morphology\DTO;

use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Enums\PatternType;
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
     * and either its transliterated, phonemic, or native spelling.
     *
     * Each call to this DTO class collects the morph rules for one
     * base-particle pair. A single word form is expected to call it
     * 3 or more times, varying PatternType and/or neographyId each
     * time.
     *
     * The DTO takes this form:
     * {
     *     "base" => <string>,
     *     "particle" => <string>,
     *     "baseRegExs" => [
     *         [
     *             "pattern" => <string>,
     *             "replacement" => <string>
     *         ],
     *         ...
     *     ],
     *     "particleRegExs" => [
     *         [
     *             "pattern" => <string>,
     *             "replacement" => <string>
     *         ],
     *         ...
     *     ],
     *     "template" => <string>
     * }
     */
    public static function fromRow(
        InflectionTableRow $row,
        string $base,
        PatternType $type,
        /**
         * This is only used if $type = PatternType::Native
         */
        int $neographyId = null,
    ): self
    {
        if ($row->src_particle === null) {
            throw new \LogicException('Can\'t auto-inflect with a null source particle.');
        }
        if ($type == PatternType::Native && $neographyId === null) {
            throw new \InvalidArgumentException('AutoInflector called in Native mode with a null neographyId.');
        }
        $row->loadMissing([
            'sourceParticle.nativeSpellings',
            'morphRules',
        ]);
        $form = $row->sourceParticle;
        $particleString = match($type) {
            PatternType::Transliterated => $form->transliterated,
            PatternType::Phonemic => $form->phonemic,
            PatternType::Native => $form
                ->nativeSpellings
                ->first(fn($t)=>$t->neography_id==$neographyId)
                ->spelling ?? null,
        };
        if ($particleString === null) {
            throw new \LogicException('The particle `Form` object has no native spelling!');
        }
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