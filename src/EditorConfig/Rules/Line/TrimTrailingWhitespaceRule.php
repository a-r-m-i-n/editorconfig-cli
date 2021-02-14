<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\Line;

use Armin\EditorconfigCli\EditorConfig\Rules\AbstractRule;
use Armin\EditorconfigCli\EditorConfig\Utility\LineEndingUtility;

class TrimTrailingWhitespaceRule extends AbstractRule
{
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
