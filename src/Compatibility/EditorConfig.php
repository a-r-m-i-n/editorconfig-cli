<?php

declare(strict_types = 1);

namespace Idiosyncratic\EditorConfig;

use function array_merge;
use function array_pop;
use const DIRECTORY_SEPARATOR;
use function dirname;
use function is_file;
use function is_readable;
use function realpath;
use function sprintf;

final class EditorConfig
{
    /** @var array<string, EditorConfigFile> */
    private $configFiles = [];

    /** @var string|null */
    private $rootPath;

    public function __construct($rootPath = null)
    {
        if ($rootPath) {
            $this->rootPath = realpath($rootPath);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfigForPath(string $path): array
    {
        $configFiles = $this->locateConfigFiles($path);

        $root = false;

        $configuration = [];

        $configFile = array_pop($configFiles);

        while (null !== $configFile) {
            $configuration = array_merge($configuration, $configFile->getConfigForPath($path));
            $configFile = array_pop($configFiles);
        }

        foreach ($configuration as $key => $declaration) {
            if (null !== $declaration->getValue()) {
                continue;
            }

            unset($configuration[$key]);
        }

        return $configuration;
    }

    /**
     * @return array<EditorConfigFile>
     */
    private function locateConfigFiles(string $path): array
    {
        $files = [];

        $stop = false;

        $parent = '';

        while ($parent !== $path) {
            $editorConfigFile = realpath(sprintf('%s%s.editorconfig', $path, DIRECTORY_SEPARATOR));

            if (false !== $editorConfigFile && is_file($editorConfigFile) && is_readable($editorConfigFile)) {
                $file = $this->getConfigFile($editorConfigFile);

                if (empty($this->rootPath) || !$file->isRoot() || dirname($file->getPath()) === $this->rootPath) {
                    $files[] = $file;
                }

                if (true === $file->isRoot()) {
                    break;
                }
            }

            $path = dirname($path);
            $parent = dirname($path);
        }

        return $files;
    }

    private function getConfigFile(string $path): EditorConfigFile
    {
        return $this->configFiles[$path] ?? $this->configFiles[$path] = new EditorConfigFile($path);
    }
}
