<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig;

use Armin\EditorconfigCli\EditorConfig\Rules\FileResult;
use Armin\EditorconfigCli\EditorConfig\Rules\Validator;
use Armin\EditorconfigCli\EditorConfig\Utility\MimeTypeUtility;
use Armin\EditorconfigCli\EditorConfig\Utility\TimeTrackingUtility;
use Idiosyncratic\EditorConfig\EditorConfig;
use Symfony\Component\Finder\Finder;

class Scanner
{
    /**
     * @var string|null
     */
    private $rootPath;

    /**
     * @var array
     */
    private $skippingRules;

    /**
     * @var EditorConfig
     */
    private $editorConfig;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var array|string[]
     */
    private $skippedBinaryFiles = [];

    public function __construct(?EditorConfig $editorConfig = null, ?Validator $validator = null, string $rootPath = null, array $skippingRules = [])
    {
        $this->editorConfig = $editorConfig ?? new EditorConfig();
        $this->validator = $validator ?? new Validator();
        $this->rootPath = $rootPath;
        $this->skippingRules = $skippingRules;
    }

    public function setRootPath(?string $rootPath): void
    {
        if ($rootPath) {
            $this->rootPath = realpath($rootPath) ?: null;
        }
    }

    public function getSkippingRules(): array
    {
        return $this->skippingRules;
    }

    public function setSkippingRules(array $skippingRules): void
    {
        $this->skippingRules = $skippingRules;
    }

    /**
     * @return array<string, string> Key is file path, value is guessed mime-type
     */
    public function getSkippedBinaryFiles(): array
    {
        return $this->skippedBinaryFiles;
    }

    /**
     * @param bool $strict when true, any difference of indention size is spotted
     *
     * @return array|FileResult[]
     */
    public function scan(Finder $finderInstance, bool $strict = false, callable $tickCallback = null, bool $collectBinaryFiles = false): array
    {
        $results = [];
        foreach ($finderInstance as $file) {
            $config = $this->editorConfig->getConfigForPath((string)$file->getRealPath());

            $fileResult = $this->validator->createValidatedFileResult($file, $config, $strict, $this->skippingRules);
            $filePath = $fileResult->getFilePath();
            if (!empty($this->rootPath)) {
                $filePath = substr($filePath, strlen($this->rootPath));
            }
            if (!$fileResult->isBinary()) {
                $results[$filePath] = $fileResult;
            } elseif ($collectBinaryFiles) {
                $this->skippedBinaryFiles[$filePath] = MimeTypeUtility::guessMimeType($fileResult->getFilePath());
            }
            if ($tickCallback) {
                $tickCallback($fileResult);
            }
        }
        TimeTrackingUtility::addStep('Scan finished');

        return $results;
    }
}
