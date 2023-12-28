<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Utility;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FinderUtility
{
    private static array $currentExcludes = [];

    /**
     * Creates new Symfony Finder instance based on given config.
     */
    public static function createByFinderOptions(array $finderOptions, string $gitOnly = null): Finder
    {
        if (!empty($gitOnly)) {
            return self::buildGitOnlyFinder($finderOptions['path'], $gitOnly);
        }

        return self::buildFinderByCliArguments($finderOptions);
    }

    /**
     * Using finderOptions array to build Finder instance.
     */
    protected static function buildFinderByCliArguments(array $finderOptions): Finder
    {
        self::$currentExcludes = $finderOptions['exclude'];

        $finder = new Finder();

        return $finder
            ->files()
            ->ignoreVCS(true)
            ->ignoreVCSIgnored(!$finderOptions['disable-auto-exclude'] && is_readable($finderOptions['path'] . '/.gitignore'))
            ->name($finderOptions['names'])
            ->notPath(self::$currentExcludes)
            ->in($finderOptions['path']);
    }

    /**
     * Calling local git binary to identify files known to Git.
     * Used, when --git-only (-g) is set.
     */
    protected static function buildGitOnlyFinder(string $workingDir, string $gitOnlyCommand): Finder
    {
        exec('cd ' . $workingDir . ' && ' . $gitOnlyCommand . ' 2>&1', $result, $returnCode);
        if (0 !== $returnCode) {
            throw new \RuntimeException('Git binary returned error code ' . $returnCode . ' with the following output:' . PHP_EOL . PHP_EOL . implode(PHP_EOL, $result), $returnCode);
        }

        $finder = new Finder();
        if (!empty($result)) {
            $iterator = [];
            foreach ($result as $item) {
                // Check for quotepath, containing octal values for special chars
                if ('"' === substr($item, 0, 1) && '"' === substr($item, -1)) {
                    $item = substr($item, 1, -1);
                    // Convert octal values to special chars
                    $item = preg_replace_callback('/\\\\(\d{3})/', static function ($matches) {
                        return chr((int)octdec($matches[1]));
                    }, $item);
                }

                $iterator[] = new SplFileInfo($workingDir . '/' . $item, $item, $item);
            }
            $finder->append($iterator);
        } else {
            $finder->append([]);
        }

        return $finder->files()->ignoreVCS(true);
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
            $finder->files();

            return $finder;
        };

        return $closure($finderConfigPath);
    }

    public static function getCurrentExcludes(): array
    {
        return self::$currentExcludes;
    }
}
