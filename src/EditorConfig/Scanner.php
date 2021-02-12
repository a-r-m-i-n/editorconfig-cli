<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig;

use Armin\EditorconfigCli\EditorConfig\Rules\FileResult;
use Armin\EditorconfigCli\EditorConfig\Rules\Validator;
use Idiosyncratic\EditorConfig\EditorConfig;
use Symfony\Component\Finder\Finder;

class Scanner
{
    private EditorConfig $editorConfig;
    private Validator $validator;

    public function __construct(?EditorConfig $editorConfig = null, ?Validator $validator = null)
    {
        $this->editorConfig = $editorConfig ?? new EditorConfig();
        $this->validator = $validator ?? new Validator();
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
            if (!$fileResult->isValid()) {
                $results[$fileResult->getFilePath()] = $fileResult;
            }
            if ($tickCallback) {
                $tickCallback($fileResult);
            }
        }

        return $results;
    }
}
