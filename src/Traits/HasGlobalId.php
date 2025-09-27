<?php

namespace PeterMarkley\Tollerus\Traits;

trait HasGlobalId
{
    public $incrementing = false;
    protected $keyType = 'int';

    protected static function bootHasGlobalId(): void
    {
        /**
         * We would use directly assign these, except we don't want to
         * overwrite any pre-existing contents.
         */
        static::retrieved(function ($model) {
            $model->appends = array_unique(
                array_merge($model->appends, ['global_id'])
            );
            $model->fillable = array_unique(
                array_merge($model->fillable, ['id'])
            );
        });
    }

    public static function isValidGlobalId(string $str): bool
    {
        /**
         * RFC 4648 https://datatracker.ietf.org/doc/html/rfc4648
         * Check against safe charset of §5 filename-safe format
         */
        $pattern = "/^[A-Za-z0-9\-_]+$/";
        if (!preg_match($pattern, $str)) {
            return false;
        }

        // Check length. Maximum is 5 before PHP int overflow
        if (strlen($str) > 5) {
            return false;
        }

        return true;
    }

    private static function EncodeGlobalId(int $num): string
    {
        /**
         * PHP's normal (signed) integer type has a maximum value of
         * 2^31-1, because it's 32 bits with one reserved for the sign.
         * The most base64 digits we can get from that is 5, because
         * base64 takes 6 bits per digit and 5*6=30.
         * 
         * However, the base64 utility works in multiples of 24 bits
         * (4 base64 digits). So we basically need to inflate to
         * 48 bits, then trim back down afterward.
         */
        // Sanity check input
        if ($num < 0 || $num > 0x3FFFFFFF) { // 2^30 - 1
            throw new InvalidArgumentException('GlobalId supports only 0..(2^30-1).');
        }

        // Read 32 bits
        $binary32 = pack("N",$num);

        // Inflate to multiple of 24
        $binary48 = str_pad($binary32, 6, "\0", STR_PAD_LEFT);

        // Encode
        $encoded = base64_encode($binary48);

        /**
         * Trim leading 'A's to a minimum of 1. This can be re-padded
         * later to any desired length.
         */
        $trimmed = str_pad(ltrim($encoded, "A"), 1, "A");

        /**
         * The PHP function outputs §4 format, but we are
         * using §5 format. So we convert.
         */
        $converted = strtr(
            rtrim($trimmed, '='),
            '+/',
            '-_'
        );

        return $converted;
    }
    
    private static function DecodeGlobalId(string $input): int
    {
        /**
         * Just as above, we need to inflate to 48 bits then trim
         * back down to 32 after decoding.
         */
        // Sanity check input
        if (!self::isValidGlobalId($input)) {
            throw new InvalidArgumentException('GlobalId supports only RFC 4648 §5 characters (url-safe base64), length 1-5.');
        }

        // We can't use more than 5 input digits (30 bits)
        $trimmed = substr($input, -5);

        // Inflate to multiple of 4 digits (48 bits)
        $inflated = str_pad($trimmed, 8, "A", STR_PAD_LEFT);

        /**
         * We are using the §5 format, but the PHP function
         * expects §4 format. So we convert.
         */
        $converted = strtr($inflated, '-_', '+/');

        // Decode
        $binary48 = base64_decode($converted);

        // Trim to 32 bits
        $binary32 = substr($binary48, -4);

        // Build integer
        $num = unpack("Nint",$binary32)["int"];

        return $num;
    }

    /**
     * Use encode/decode methods to expose mutated ID as an attribute.
     */
    protected function globalId(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) =>
                isset($attributes['id']) ? self::EncodeGlobalId((int) $attributes['id']) : null,

            set: fn ($value) => ['id' => self::DecodeGlobalId($value)]
        );
    }
}

