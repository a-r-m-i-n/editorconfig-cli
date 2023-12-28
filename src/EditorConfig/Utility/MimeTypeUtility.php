<?php

namespace Armin\EditorconfigCli\EditorConfig\Utility;

use Symfony\Component\Mime\MimeTypes;

class MimeTypeUtility
{
    public static function guessMimeType(string $filePath): string
    {
        $mime = new MimeTypes();

        return (string)$mime->guessMimeType($filePath);
    }

    public static function isCommonTextType(string $filePath): bool
    {
        $mimeType = self::guessMimeType($filePath);
        if (str_starts_with($mimeType, 'text/')) {
            return true;
        }

        if (str_starts_with($mimeType, 'application/')) {
            if (str_ends_with($mimeType, 'script')) {
                return true;
            }
            if (str_ends_with($mimeType, 'json')) {
                return true;
            }
            if (str_ends_with($mimeType, 'yaml')) {
                return true;
            }
            if (str_ends_with($mimeType, 'xml')) {
                return true;
            }
            if (str_ends_with($mimeType, 'sql')) {
                return true;
            }
        }

        return false;
    }

    public static function isCommonBinaryType(string $filePath): bool
    {
        $mimeType = self::guessMimeType($filePath);
        if (str_starts_with($mimeType, 'font/')) {
            return true;
        }
        if ('image/svg' !== $mimeType && str_starts_with($mimeType, 'image/')) {
            return true;
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return true;
        }
        if (str_starts_with($mimeType, 'video/')) {
            return true;
        }
        if (str_starts_with($mimeType, 'application/vnd.')) {
            return true;
        }
        if ('application/pdf' === $mimeType) {
            return true;
        }
        if ('application/msword' === $mimeType) {
            return true;
        }
        if ('application/rtf' === $mimeType) {
            return true;
        }
        if ('application/zip' === $mimeType) {
            return true;
        }
        if ('application/tar' === $mimeType) {
            return true;
        }
        if ('application/bzip2' === $mimeType) {
            return true;
        }
        if ('application/octet-stream' === $mimeType) {
            return true;
        }
        if ('application/wasm' === $mimeType) {
            return true;
        }
        if (str_starts_with($mimeType, 'application/')) {
            if (str_ends_with($mimeType, '-compressed')) {
                return true;
            }
            if (str_ends_with($mimeType, '-ttf')) {
                return true;
            }
            if (str_ends_with($mimeType, '-archive')) {
                return true;
            }
        }

        return false;
    }

    public static function isBinaryFileType(string $filePath, float $threshold = .9): bool
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \RuntimeException('Unable to check file "' . $filePath . '" for being binary!');
        }
        $length = strlen($content);

        if (0 === $length) {
            return false;
        }

        $printableCount = 0;

        for ($i = 0; $i < $length; ++$i) {
            $ord = ord($content[$i]);

            // Printable ASCII chars (32-126), Tabulator (9), CR (10) and LF (13)
            if (($ord >= 32 && $ord <= 126) || 9 === $ord || 10 === $ord || 13 === $ord) {
                ++$printableCount;
            }
        }

        return ($printableCount / $length) < $threshold;
    }
}
