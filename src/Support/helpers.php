<?php

use Illuminate\Support\Facades\Lang;

use PeterMarkley\Tollerus\Support\Markup\BodyTextRenderer;

if (!function_exists('tollerus_tr_optional')) {
    /**
     * Return a translation or null.
     * - If key missing => null
     * - If present but empty string => null
     * - Otherwise => string value
     */
    function tollerus_tr_optional(string $key, array $replace = []): ?string
    {
        if (!(Lang::has($key))) {
            return null;
        }
        $value = __($key, $replace);
        return $value === '' ? null : $value;
    }
}

if (!function_exists('tollerus_body_text')) {
    function tollerus_body_text(string $html): string
    {
        return app(BodyTextRenderer::class)->render($html);
    }
}
