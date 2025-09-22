<?php

namespace PeterMarkley\Tollerus\Traits;

trait HasGlobalId
{
    /**
     * RFC 4648 https://datatracker.ietf.org/doc/html/rfc4648
     * Safe charset of §5 filename-safe format
     */
    const GLOBAL_ID_SAFE_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";
    const GLOBAL_ID_MAX_SIZE = 16; //maximum number of base64url digits allowed, finite for security reasons

    private static function GlobalIdEncode(int $num): string
    {
        $binary = pack("N@3",$num<<8);

        $base64Sect4 = base64_encode($binary);
        
        /**
         * The PHP function outputs §4 format, but we are
         * using §5 format. So we convert.
         */
        $base64Sect5 = strtr(
            rtrim($base64Sect4, '='),
            '+/',
            '-_'
        );
        
        return $base64Sect5;
    }
    
    private static function GlobalIdDecode(string $base64Sect5): int
    {
        /**
         * One byte is 8 bits, whereas one base64 character
         * is 6 bits. Thus the least common multiple is
         * 12 bits, which is 2 base64 chars or 3 bytes.
         * 
         * The pack() utility can only take offsets given in
         * bytes, so we need to use that LCM here as part of
         * the conversion process.
         */
        $base64Multiplied = $base64Sect5."AA";

        /**
         * We are using the §5 format, but the PHP function
         * expects §4 format. So we convert.
         */

        // Ensure new length is multiple of 4
        $newLength = ceil(strlen($base64Multiplied)/4)*4;

        // Pad with '='s and substitute characters
        $base64Sect4 = str_pad(
            strtr($base64Multiplied, '-_', '+/'),
            $newLength,
            '=',
            STR_PAD_RIGHT
        );

        $binary = base64_decode($base64Sect4);

        $num = unpack("Nint",$binary)["int"]>>8;

        return $num;
    }
}

