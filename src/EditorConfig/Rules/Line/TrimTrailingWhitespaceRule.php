<?php

declare(strict_types = 1);

namespace FGTCLB\EditorConfig\EditorConfig\Rules\Line;

use FGTCLB\EditorConfig\EditorConfig\Rules\AbstractRule;
use FGTCLB\EditorConfig\EditorConfig\Utility\LineEndingUtility;

class TrimTrailingWhitespaceRule extends AbstractRule
{
    public function __construct(string $filePath, string $fileContent)
    {
        parent::__construct($filePath, $fileContent);
    }

    protected function validate(string $content): bool
    {
        $lineEnding = LineEndingUtility::detectLineEnding($content, false);
        if (empty($lineEnding)) {
            $lineEnding = "\n";
        }
        /** @var array|string[] $lines */
        $lines = explode($lineEnding, $content);

        $lineCount = 0;
        $isValid = true;
        foreach ($lines as $line) {
            ++$lineCount;

            $trim = rtrim($line);
            if ($trim !== $line) {
                $this->addError($lineCount, 'Trailing whitespaces found.');
            }
        }

        return $isValid;
    }

    public function fixContent(string $content): string
    {
        $lineEnding = LineEndingUtility::detectLineEnding($content, false) ?: "\n";
        /** @var array|string[] $lines */
        $lines = explode($lineEnding, $content);
        foreach ($lines as $no => $line) {
            $updatedLine = rtrim($line);
            $lines[$no] = $updatedLine;
        }

        return implode($lineEnding, $lines);
    }
}
