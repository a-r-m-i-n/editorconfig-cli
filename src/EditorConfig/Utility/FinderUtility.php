<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Utility;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\FilenameFilterIterator;
use Symfony\Component\Finder\SplFileInfo as FinderSplFileInfo;

class FinderUtility
{
    /**
     * @var string[]
     */
    private static array $currentExcludes = [];

    /**
     * Creates new Symfony Finder instance based on given config.
     *
     * @param array<string, mixed> $finderOptions
     */
    public static function createByFinderOptions(array $finderOptions, ?string $gitOnly = null): Finder
    {
        if (!empty($gitOnly)) {
            return self::buildGitOnlyFinder($finderOptions['path'], $gitOnly, $finderOptions['names']);
        }

        return self::buildFinderByCliArguments($finderOptions);
    }

    /**
     * Using finderOptions array to build Finder instance.
     *
     * @param array<string, mixed> $finderOptions
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
     *
     * @param string[] $names
     */
    protected static function buildGitOnlyFinder(string $workingDir, string $gitOnlyCommand, array $names): Finder
    {
        exec('cd ' . $workingDir . ' && ' . $gitOnlyCommand . ' 2>&1', $result, $returnCode);
        if (0 !== $returnCode) {
            throw new \RuntimeException('Git binary returned error code ' . $returnCode . ' with the following output:' . PHP_EOL . PHP_EOL . implode(PHP_EOL, $result), $returnCode);
        }

        $files = [];

        foreach ($result as $item) {
            // Check for quotepath, containing octal values for special chars
            if (str_starts_with($item, '"') && str_ends_with($item, '"')) {
                $item = substr($item, 1, -1);
                // Convert octal values to special chars
                $item = preg_replace_callback(
                    '/\\\\(\d{3})/',
                    static fn ($matches) => chr((int)octdec((string)$matches[1])),
                    $item
                );
            }

            if (!is_string($item) || empty($item)) {
                continue;
            }

            $files[] = new FinderSplFileInfo(
                $workingDir . '/' . $item,
                dirname($item),
                $item
            );
        }

        $iterator = new \ArrayIterator($files);

        if (!empty($names)) {
            $iterator = new FilenameFilterIterator($iterator, $names, []);
        }

        $finder = new Finder();
        $finder->files()->ignoreVCS(true);

        $finder->append($iterator);

        return $finder;
    }

    /**
     * Requires given PHP file (in isolated closure scope) and expect a Symfony Finder instance to be returned.
     * Also, the passed $finderOptions will be available in code of required PHP file (scoped and as global variable).
     *
     * @param array<string, mixed> $finderOptions
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
                    $returnType = 'instance of ' . $finder::class;
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

    /**
     * @return string[]
     */
    public static function getCurrentExcludes(): array
    {
        return self::$currentExcludes;
    }
}
