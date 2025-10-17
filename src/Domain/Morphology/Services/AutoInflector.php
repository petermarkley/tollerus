<?php

namespace PeterMarkley\Tollerus\Domain\Morphology\Services;

use PeterMarkley\Tollerus\Enums\MorphRulePatternType;
use PeterMarkley\Tollerus\Domain\Morphology\DTO\AutoInflectorInput;
use PeterMarkley\Tollerus\Models\InflectionTableRow;

final class AutoInflector
{
    /**
     * Returns an inflected suggestion
     */
    public static function suggest(AutoInflectorInput $input): string
    {
        $newBase     = self::batchReplace($input->base, $input->baseRegExs);
        $newParticle = self::batchReplace($input->particle, $input->particleRegExs);
        return self::applyTemplate($newBase, $newParticle, $input->template);
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
                $output = preg_replace(
                    $args['pattern'],
                    $args['replacement'],
                    $carry
                ) ?? $carry;
            } catch (\Exception $e) {
                // Errors should pass silently
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
        // FIXME - this is dumb but works as a placeholder for now
        return $base . $particle;
    }

    /**
     * Convenience function that calls the DTO
     */
    public static function suggestFromRow(
        InflectionTableRow $row,
        string $base,
        MorphRulePatternType $type,
        /**
         * This is only used if $type = MorphRulePatternType::Native
         */
        int $neographyId = null,
    ): string
    {
        $dto = AutoInflectorInput::fromRow(
            row: $row,
            base: 'exhuberate',
            type: MorphRulePatternType::Native,
            neographyId: 1,
        );
        return AutoInflector::suggest($dto);
    }
}