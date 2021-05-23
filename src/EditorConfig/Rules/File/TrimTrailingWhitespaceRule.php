<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\Rule;
use Armin\EditorconfigCli\EditorConfig\Utility\LineEndingUtility;

class TrimTrailingWhitespaceRule extends Rule
{
    /**
     * @var bool
     */
    private $insertFinalNewLine;

    public function __construct(string $filePath, string $fileContent, bool $insertFinalNewLine)
    {
        $this->insertFinalNewLine = $insertFinalNewLine;
        parent::__construct($filePath, $fileContent);
    }

    protected function validate(string $content): bool
    {
        $trim = rtrim($content);
        if ($this->insertFinalNewLine) {
            $insertFinalNewLineRule = new InsertFinalNewLineRule($this->filePath, $content);
            if ($insertFinalNewLineRule->isValid()) {
                $trim .= LineEndingUtility::detectLineEnding($content, false) ?: "\n";
            }
        }
        if ($content !== $trim) {
            $this->addError(null, 'This file has trailing whitespaces.');

            return false;
        }

        return true;
    }

    public function fixContent(string $content): string
    {
        $trim = rtrim($content);
        if ($this->insertFinalNewLine) {
            $trim .= LineEndingUtility::detectLineEnding($content, false) ?: "\n";
        }

        return $trim;
    }
}
