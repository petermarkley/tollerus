<?php

namespace PeterMarkley\Tollerus\Domain\Morphology\Services;

use PeterMarkley\Tollerus\Enums\MorphRulePatternType;
use PeterMarkley\Tollerus\Domain\Morphology\DTO\AutoInflectorInput;
use PeterMarkley\Tollerus\Models\InflectionRow;

final class AutoInflector
{
    private AutoInflectorInput $input;

    public function __construct(
        InflectionRow $row,
        string $base,
        MorphRulePatternType $type,
        int $neographyId = null,
    ) {
        $this->input = AutoInflectorInput::fromRow(
            row: $row,
            base: $base,
            type: $type,
            neographyId: $neographyId,
        );
    }

    /**
     * Returns an inflected suggestion
     */
    public function inflect(): string
    {
        $newBase     = self::batchReplace($this->input->base, $this->input->baseRegExs);
        $newParticle = self::batchReplace($this->input->particle, $this->input->particleRegExs);
        return self::applyTemplate($newBase, $newParticle, $this->input->template);
    }

    /**
     * Perform a set of cumulative RegEx replacements on a string
     */
    private static function batchReplace(
        string $subject,
        array $regExArgs,
    ): string
    {
        return collect($regExArgs)->reduce(function ($carry, $args) {
            try {
                $output = mb_ereg_replace(
                    $args['pattern'],
                    $args['replacement'],
                    $carry
                ) ?? $carry;
            } catch (\Exception $e) {
                // Errors should pass silently (for now)
                $output = $carry;
            }
            return $output;
        }, $subject);
    }

    /**
     * Apply the template to receive the final inflected word form
     */
    private static function applyTemplate(
        string $base,
        string $particle,
        string $template,
    ): string
    {
        /**
         * The template accepts the tokens `{B}` for the base, and
         * `{P}` for the particle.
         */
        return strtr($template, [
            '{B}' => $base,
            '{P}' => $particle,
        ]);
    }
}