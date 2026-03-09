<?php

namespace PeterMarkley\Tollerus\Enums;

enum MorphRuleTargetType: string
{
    case BaseInput = 'base_input';
    case ParticleInput = 'particle_input';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function localize(): string
    {
        return match ($this) {
            self::BaseInput     => __('tollerus::ui.base'),
            self::ParticleInput => __('tollerus::ui.particle'),
        };
    }
}