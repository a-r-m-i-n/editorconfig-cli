<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\Rule;
use Armin\EditorconfigCli\EditorConfig\Utility\LineEndingUtility;

class InsertFinalNewLineRule extends Rule
{
    /**
     * @var string
     */
    private $newLineFormat;

    public function __construct(string $filePath, string $fileContent, ?string $newLineFormat = null)
    {
        if (null === $newLineFormat) {
            $newLineFormat = LineEndingUtility::detectLineEnding($fileContent, false);
        }

        $this->newLineFormat = $newLineFormat ?? "\n";
        parent::__construct($filePath, $fileContent);
    }

    protected function validate(string $content): bool
    {
        $lastChar = substr($content, -1);
        $result = in_array($lastChar, ["\r", "\n"], true);
        if (!$result) {
            $this->addError(null, 'This file has no final new line given');
        }

        return $result;
    }

    public function fixContent(string $content): string
    {
        return rtrim($content) . $this->newLineFormat;
    }
}
