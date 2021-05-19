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
     * Creates new Symfony Finder instance by given finderOptions array.
     */
    public static function createByFinderOptions(array $finderOptions): Finder
    {
        $excludePaths = $finderOptions['exclude'];

        if (!$finderOptions['disable-auto-exclude']) {
            $excludePaths[] = 'vendor';
            $excludePaths[] = 'node_modules';
        }

        self::$currentExcludes = $excludePaths;

        $finder = new Finder();
        $finder
            ->files()
            ->ignoreVCS(true)
            ->name($finderOptions['names'])
            ->notPath($excludePaths)
            ->in($finderOptions['path']);

        return $finder;
    }

    /**
     * Requires given PHP file (in isolated closure scope) and expect a Symfony Finder instance to be returned.
     * Also, the passed $finderOptions will be available in code of required PHP file (scoped and as global variable).
     */
    public static function loadCustomFinderInstance(
        string $finderConfigPath,
        array $finderOptions
    ): Finder {
        if (!file_exists($finderConfigPath)) {
            throw new \RuntimeException(sprintf('Finder config file "%s" not found!', $finderConfigPath), 1621342890);
        }

        $closure = function (string $finderConfigPath) use ($finderOptions): Finder {
            // Load custom php in isolated closure scope, providing variable $finderOptions
            $GLOBALS['finderOptions'] = $finderOptions;
            $finder = require $finderConfigPath;
            if (!is_object($finder) || !$finder instanceof Finder) {
                if (is_object($finder)) {
                    $returnType = 'instance of ' . get_class($finder);
                } else {
                    $returnType = gettype($finder);
                }
                throw new \RuntimeException('Custom Symfony Finder configuration (' . $finderConfigPath . ") should return an instance of \Symfony\Component\Finder\Finder. \nInstead it returns: " . $returnType, 1621343069);
            }

            return $finder;
        };

        return $closure($finderConfigPath);
    }

    public static function getCurrentExcludes(): array
    {
        return self::$currentExcludes;
    }
}
