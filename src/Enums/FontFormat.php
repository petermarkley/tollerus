<?php

namespace PeterMarkley\Tollerus\Enums;

enum FontFormat: string
{
    case Svg = 'svg';
    case Ttf = 'ttf';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * System info
     */
    public function extension(): string
    {
        return match ($this) {
            self::Svg => 'svg',
            self::Ttf => 'ttf',
        };
    }
    public function mimeTypes(): array
    {
        return match ($this) {
            self::Svg => ['image/svg+xml'],
            self::Ttf => ['font/ttf', 'font/sfnt'],
        };
    }
    public function cssFormatKey(): string
    {
        /**
         * For use with a CSS `src` property inside
         * a `@font-face` rule.
         *
         * See here:
         * https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/At-rules/@font-face/src#font_formats
         */
        return match ($this) {
            self::Svg => 'svg',
            self::Ttf => 'truetype',
        };
    }
    public function preferenceOrder(): int
    {
        /**
         * Used for sorting so that best / most
         * compatible formats are shown to browsers
         * first. Lower number = more preferred.
         */
        return match ($this) {
            self::Svg => 5,
            self::Ttf => 0,
        };
    }

    /**
     * Text for human reading
     */
    public function nameBrief(): string
    {
        return match ($this) {
            self::Svg => 'SVG',
            self::Ttf => 'TTF',
        };
    }
    public function localizeNameFull(): string
    {
        return match ($this) {
            self::Svg => __('tollerus::ui.scalable_vector_graphics'),
            self::Ttf => __('tollerus::ui.truetype_font'),
        };
    }
    public function localizeFormat(): string
    {
        return match ($this) {
            self::Svg => __('tollerus::ui.svg_format'),
            self::Ttf => __('tollerus::ui.ttf_format'),
        };
    }

    /**
     * These functions can be used to dereference database
     * column names from an Enum instance.
     *
     * Example:
     *
     *   $fontFormat = FontFormat::from('svg');
     *   $filePath = $neography->{$fontFormat->pathColumn()};
     *
     * In the above, $filePath will then have the contents
     * of $neography->font_svg_file_path.
     */
    public function blobColumn(): string
    {
        return match ($this) {
            self::Svg => 'font_svg',
            self::Ttf => 'font_ttf',
        };
    }
    public function pathColumn(): string
    {
        return match ($this) {
            self::Svg => 'font_svg_file_path',
            self::Ttf => 'font_ttf_file_path',
        };
    }
    public function urlColumn(): string
    {
        return match ($this) {
            self::Svg => 'font_svg_url',
            self::Ttf => 'font_ttf_url',
        };
    }
}