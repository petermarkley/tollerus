<?php

namespace PeterMarkley\Tollerus\Domain\Neography\Services;

use PeterMarkley\Tollerus\Enums\FontFormat;
use PeterMarkley\Tollerus\Enums\WritingDirection;
use PeterMarkley\Tollerus\Models\Neography;

final class FontCssService
{
    /**
     * If the given Neography has a published font, this returns
     * the CSS to define a custom font face for it.
     */
    public function getFontFaceStyle(Neography $neography): string
    {
        // Check for published fonts
        $hasPublishedFont = false;
        foreach (FontFormat::cases() as $fontFormat) {
            if (!empty($neography->{$fontFormat->urlColumn()})) {
                $hasPublishedFont = true;
                break;
            }
        }

        // If none are found, return nothing
        if (!$hasPublishedFont) {
            return '';
        }

        // Prepare some variables
        $srcList = collect(FontFormat::cases())
            ->sortBy(fn($f) => $f->preferenceOrder())
            ->map(function ($fontFormat) use ($neography) {
                if (empty($neography->{$fontFormat->urlColumn()})) {
                    return '';
                }
                $url = $neography->{$fontFormat->urlColumn()};
                $formatKey = $fontFormat->cssFormatKey();
                return "url('{$url}') format('{$formatKey}')";
            })->filter(fn ($f) => !empty($f))
            ->implode(', ');
        $familyName = $neography->machine_name;
        if ($neography->direction_primary == WritingDirection::RightToLeft) {
            $writingDirectionProps = 'direction: rtl; unicode-bidi: bidi-override;';
        } else {
            $writingDirectionProps = '';
        }

        // Build final CSS output
        $style = <<<CSS
        @font-face {
            font-family: {$familyName};
            src: {$srcList};
        }
        .tollerus_{$familyName} {
            font-family: {$familyName};
            {$writingDirectionProps}
        }
        CSS;

        return $style;
    }
}