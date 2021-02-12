<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Utility;

class FileEncodingUtility
{
    public static function getUTF8ByteOrderMark(): string
    {
        return chr(0xEF) . chr(0xBB) . chr(0xBF);
    }

    public static function getUTF16BEByteOrderMark(): string
    {
        return chr(0xFE) . chr(0xFF);
    }

    public static function getUTF16LEByteOrderMark(): string
    {
        return chr(0xFF) . chr(0xFE);
    }

    public static function detect(string $text): ?string
    {
        $first2 = substr($text, 0, 2);
        $first3 = substr($text, 0, 3);

        if (self::getUTF8ByteOrderMark() === $first3) {
            return 'utf-8-bom';
        }
        if (self::getUTF16BEByteOrderMark() === $first2) {
            return 'utf-16be';
        }
        if (self::getUTF16LEByteOrderMark() === $first2) {
            return 'utf-16le';
        }

        $encoding = strtolower(mb_detect_encoding($text, 'UTF-8,UTF-16BE,UTF-16LE,ISO-8859-1') ?: 'utf-8');
        if ('iso-8859-1' === $encoding) {
            $encoding = 'latin1';
        }

        return $encoding;
    }
}
