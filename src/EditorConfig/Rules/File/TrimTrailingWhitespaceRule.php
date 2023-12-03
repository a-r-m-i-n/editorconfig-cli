<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\Rule;
use Armin\EditorconfigCli\EditorConfig\Utility\LineEndingUtility;

class TrimTrailingWhitespaceRule extends Rule
{
    private bool $insertFinalNewLine;

    public function __construct(string $filePath, string $fileContent, bool $insertFinalNewLine)
    {
        $this->insertFinalNewLine = $insertFinalNewLine;
        parent::__construct($filePath, $fileContent);
    }

    protected function validate(string $content): void
    {
        $trim = rtrim($content);
        if ('' === $content) {
            return;
        }
        if ($this->insertFinalNewLine) {
            $insertFinalNewLineRule = new InsertFinalNewLineRule($this->filePath, $content);
            if ($insertFinalNewLineRule->isValid()) {
                $trim .= LineEndingUtility::detectLineEnding($content, false) ?: "\n";
            }
        }
        if ($content !== $trim) {
            $this->addError(null, 'This file has trailing whitespaces');
        }
    }

    public function fixContent(string $content): string
    {
        if ('' === $content) {
            return $content;
        }
        $trim = rtrim($content);
        if ($this->insertFinalNewLine) {
            $trim .= LineEndingUtility::detectLineEnding($content, false) ?: "\n";
        }

        return $trim;
    }
}
