<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Utility;

use Symfony\Component\Finder\Finder;

class FinderUtility
{
    /**
     * @param mixed $names
     */
    public static function create(string $path, $names = '*', array $excludePaths = []): Finder
    {
        $finder = new Finder();
        $finder
            ->files()
            ->ignoreVCS(true)
            ->name($names)
            ->notPath($excludePaths)
            ->in($path);

        return $finder;
    }
}
