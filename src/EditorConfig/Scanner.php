<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig;

use Armin\EditorconfigCli\EditorConfig\Rules\FileResult;
use Armin\EditorconfigCli\EditorConfig\Rules\Validator;
use Idiosyncratic\EditorConfig\EditorConfig;
use Symfony\Component\Finder\Finder;

class Scanner
{
    /**
     * @var string|null
     */
    private $rootPath;

    /**
     * @var EditorConfig
     */
    private $editorConfig;

    /**
     * @var Validator
     */
    private $validator;

    public function __construct(?EditorConfig $editorConfig = null, ?Validator $validator = null)
    {
        $this->editorConfig = $editorConfig ?? new EditorConfig();
        $this->validator = $validator ?? new Validator();
    }

    public function setRootPath(?string $rootPath): void
    {
        if (!empty($rootPath)) {
            $this->rootPath = realpath($rootPath) ?: '';
            $this->editorConfig = new EditorConfig($rootPath);
        }
    }

    /**
     * @param bool $strict when true, any difference of indention size is spotted
     *
     * @return array|FileResult[]
     */
    public function scan(Finder $finderInstance, bool $strict = false, callable $tickCallback = null): array
    {
        $results = [];
        foreach ($finderInstance as $file) {
            $config = $this->editorConfig->getConfigForPath((string)$file->getRealPath());

            $fileResult = $this->validator->createValidatedFileResult($file, $config, $strict);
            if (!$fileResult->isBinary()) {
                $filePath = $fileResult->getFilePath();
                if (!empty($this->rootPath)) {
                    $filePath = substr($filePath, strlen($this->rootPath));
                }
                $results[$filePath] = $fileResult;
            }
            if ($tickCallback) {
                $tickCallback($fileResult);
            }
        }

        return $results;
    }
}
