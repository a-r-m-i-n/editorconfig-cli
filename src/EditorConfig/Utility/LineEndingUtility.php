<?php

declare(strict_types = 1);

namespace FGTCLB\EditorConfig\EditorConfig\Utility;

class LineEndingUtility
{
    private static array $lineEndings = [
        'crlf' => "\r\n",
        'cr' => "\r",
        'lf' => "\n",
    ];

    public static function detectLineEnding(string $content, bool $humanReadable = true): ?string
    {
        $whitespacesOnly = preg_replace('/[^\r\n]/i', '', $content);
        $actualEndOfLine = substr((string)$whitespacesOnly, 0, 2);
        if (!empty($actualEndOfLine) && "\r\n" !== $actualEndOfLine) {
            $actualEndOfLine = $actualEndOfLine[0]; // first char only
        }

        return $humanReadable ? self::convertActualCharToReadable($actualEndOfLine) : $actualEndOfLine;
    }

    public static function convertReadableToActualChars(string $lineEnding): ?string
    {
        return self::$lineEndings[$lineEnding] ?? null;
    }

    public static function convertActualCharToReadable(string $actualChar): ?string
    {
        $chars = array_flip(self::$lineEndings);

        return $chars[$actualChar] ?? null;
    }
}
