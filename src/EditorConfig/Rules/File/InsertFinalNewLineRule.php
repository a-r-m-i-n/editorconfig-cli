<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\AbstractRule;

class InsertFinalNewLineRule extends AbstractRule
{
    /**
     * @var string
     */
    private $newLineFormat;

    public function __construct(string $filePath, string $fileContent, string $newLineFormat = "\n")
    {
        $this->newLineFormat = $newLineFormat;
        parent::__construct($filePath, $fileContent);
    }

    protected function validate(string $content): bool
    {
        $lastChar = substr($content, -1);
        $result = in_array($lastChar, ["\r", "\n"], true);
        if (!$result) {
            $this->addError(null, 'Missing final new line.');
        }

        return $result;
    }

    public function fixContent(string $content): string
    {
        return rtrim($content) . $this->newLineFormat;
    }
}
