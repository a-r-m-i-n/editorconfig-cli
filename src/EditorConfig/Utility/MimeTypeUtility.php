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
        if (0 === strpos($mimeType, 'text/')) {
            return true;
        }

        if (0 === strpos($mimeType, 'application/')) {
            if ('script' === substr($mimeType, -strlen('script'))) {
                return true;
            }
            if ('json' === substr($mimeType, -strlen('json'))) {
                return true;
            }
            if ('yaml' === substr($mimeType, -strlen('yaml'))) {
                return true;
            }
            if ('xml' === substr($mimeType, -strlen('xml'))) {
                return true;
            }
            if ('sql' === substr($mimeType, -strlen('sql'))) {
                return true;
            }
        }

        return false;
    }

    public static function isCommonBinaryType(string $filePath): bool
    {
        $mimeType = self::guessMimeType($filePath);
        if (0 === strpos($mimeType, 'font/')) {
            return true;
        }
        if ('image/svg' !== $mimeType && 0 === strpos($mimeType, 'image/')) {
            return true;
        }
        if (0 === strpos($mimeType, 'audio/')) {
            return true;
        }
        if (0 === strpos($mimeType, 'video/')) {
            return true;
        }
        if (0 === strpos($mimeType, 'application/vnd.')) {
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
        if (0 === strpos($mimeType, 'application/')) {
            if ('-compressed' === substr($mimeType, -strlen('-compressed'))) {
                return true;
            }
            if ('-ttf' === substr($mimeType, -strlen('-ttf'))) {
                return true;
            }
            if ('-archive' === substr($mimeType, -strlen('-archive'))) {
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
