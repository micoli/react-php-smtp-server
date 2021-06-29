<?php

declare(strict_types=1);

namespace Micoli\Smtp\Server\Authentication\CramMd5;

final class HmacMd5
{
    /**
     * @see https://github.com/AOEpeople/Menta_GeneralComponents/blob/master/lib/Zend/Mail/Protocol/Smtp/Auth/Crammd5.php
     */
    public function getDigest(string $key, string $data, int $block = 64): string
    {
        if (strlen($key) > 64) {
            $key = pack('H32', md5($key));
        } elseif (strlen($key) < 64) {
            $key = str_pad($key, $block, "\0");
        }
        $k_ipad = substr($key, 0, 64) ^ str_repeat(chr(0x36), 64);
        $k_opad = substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64);
        $inner = pack('H32', md5($k_ipad.$data));
        $digest = md5($k_opad.$inner);

        return $digest;
    }
}
