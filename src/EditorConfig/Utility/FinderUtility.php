<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Utility;

use Symfony\Component\Finder\Finder;

class FinderUtility
{
    /**
     * @var array
     */
    private static $currentExcludes = [];

    /**
     * @param mixed $names
     */
    public static function create(
        string $path,
        $names = '*',
        array $excludePaths = [],
        bool $disableAutoExclude = false
    ): Finder {
        if (!$disableAutoExclude) {
            $excludePaths[] = 'vendor';
            $excludePaths[] = 'node_modules';
        }

        self::$currentExcludes = $excludePaths;

        $finder = new Finder();
        $finder
            ->files()
            ->ignoreVCS(true)
            ->name($names)
            ->notPath($excludePaths)
            ->in($path);

        return $finder;
    }

    public static function getCurrentExcludes(): array
    {
        return self::$currentExcludes;
    }
}
