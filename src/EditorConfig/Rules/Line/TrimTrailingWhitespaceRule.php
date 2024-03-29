<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\Line;

use Armin\EditorconfigCli\EditorConfig\Rules\Rule;
use Armin\EditorconfigCli\EditorConfig\Utility\LineEndingUtility;

class TrimTrailingWhitespaceRule extends Rule
{
    protected function validate(string $content): void
    {
        $lineEnding = LineEndingUtility::detectLineEnding($content, false);
        if (empty($lineEnding)) {
            $lineEnding = "\n";
        }
        /** @var array|string[] $lines */
        $lines = explode($lineEnding, $content);

        $lineCount = 0;
        foreach ($lines as $line) {
            ++$lineCount;

            $trim = rtrim($line);
            if ($trim !== $line) {
                $this->addError($lineCount, 'Trailing whitespaces found');
            }
        }
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
