<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig;

use Armin\EditorconfigCli\EditorConfig\Rules\FileResult;
use Armin\EditorconfigCli\EditorConfig\Rules\FileUnavailableException;
use Armin\EditorconfigCli\EditorConfig\Rules\Validator;
use Armin\EditorconfigCli\EditorConfig\Utility\MimeTypeUtility;
use Armin\EditorconfigCli\EditorConfig\Utility\TimeTrackingUtility;
use Idiosyncratic\EditorConfig\EditorConfig;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Scanner
{
    private readonly EditorConfig $editorConfig;
    private readonly Validator $validator;

    /**
     * @var array|string[]
     */
    private array $skippedBinaryFiles = [];

    /**
     * @var array|SplFileInfo[]
     */
    private array $unavailableFiles = [];

    /**
     * @param string[] $skippingRules
     */
    public function __construct(
        ?EditorConfig $editorConfig = null,
        ?Validator $validator = null,
        private ?string $rootPath = null,
        private array $skippingRules = []
    ) {
        $this->editorConfig = $editorConfig ?? new EditorConfig();
        $this->validator = $validator ?? new Validator();
    }

    public function setRootPath(?string $rootPath): void
    {
        if ($rootPath) {
            $this->rootPath = realpath($rootPath) ?: null;
        }
    }

    /**
     * @return array|string[]
     */
    public function getSkippingRules(): array
    {
        return $this->skippingRules;
    }

    /**
     * @param array|string[] $skippingRules
     */
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
     * @return array|SplFileInfo[]
     */
    public function getUnavailableFiles(): array
    {
        return $this->unavailableFiles;
    }

    /**
     * @param bool $strict when true, any difference of indention size is spotted
     *
     * @return array|FileResult[]
     */
    public function scan(Finder $finderInstance, bool $strict = false, ?callable $tickCallback = null, bool $collectBinaryFiles = false): array
    {
        $results = [];
        foreach ($finderInstance as $file) {
            $config = $this->editorConfig->getConfigForPath((string)$file->getRealPath());

            try {
                $fileResult = $this->validator->createValidatedFileResult($file, $config, $strict, $this->skippingRules);
            } catch (FileUnavailableException $e) {
                $this->unavailableFiles[] = $e->getUnavailableFile();
                continue;
            }

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
