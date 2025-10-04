<?php

namespace PeterMarkley\Tollerus\Traits;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasGlobalId
{
    protected static function bootHasGlobalId(): void
    {
        /**
         * We would directly assign these, except we don't want to
         * overwrite any pre-existing contents.
         */
        static::retrieved(function ($model) {
            $model->appends = array_unique(
                array_merge($model->appends, ['global_id'])
            );
        });
    }

    protected function initializeHasGlobalId(): void
    {
        $this->incrementing = false;
        $this->keyType = 'int';
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

    private static function encodeGlobalId(int $num): string
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

        // Normalize leading 'A's.
        $digits = \Config::get('tollerus.global_id_digits', 4);
        $normalized = str_pad(ltrim($encoded, "A"), $digits, "A");

        /**
         * The PHP function outputs §4 format, but we are
         * using §5 format. So we convert.
         */
        $converted = strtr(
            rtrim($normalized, '='),
            '+/',
            '-_'
        );

        return $converted;
    }
    
    private static function decodeGlobalId(string $input): int
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
            get: function ($value, array $attributes) {
                if (!isset($attributes['id'])) {
                    return null;
                }
                $globalId = self::encodeGlobalId((int) $attributes['id']);
                $digits = \Config::get('tollerus.global_id_digits', 4);
                $padded = str_pad($globalId, $digits, "A", STR_PAD_LEFT);
                return $padded;
            },

            set: fn ($value) => ['id' => self::decodeGlobalId($value)]
        );
    }
}

