<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\Line;

use Armin\EditorconfigCli\EditorConfig\Rules\Rule;
use Armin\EditorconfigCli\EditorConfig\Utility\LineEndingUtility;

class IndentionRule extends Rule
{
    private readonly string $style;

    public function __construct(
        string $filePath,
        string $fileContent,
        string $style,
        private readonly ?int $size,
        private readonly bool $strict = false
    ) {
        $this->style = strtolower($style);
        if (!in_array($this->style, ['space', 'tab'], true)) {
            throw new \InvalidArgumentException(sprintf('Unknown indention style value "%s" given in .editorconfig', $style), 1621325864);
        }
        parent::__construct($filePath, $fileContent);
    }

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
            $lineValid = true;
            $beginningWhitespaces = (string)preg_replace('/^(\s.*?)\S.*/i', '$1', $line);

            if (empty($line) || $beginningWhitespaces === $line) {
                continue;
            }

            $whitespaces = $beginningWhitespaces;
            if ('tab' === $this->style) {
                $whitespaces = str_replace(str_repeat(' ', (int)$this->size), "\t", $beginningWhitespaces);
            }

            if ('tab' === $this->style && $whitespaces !== $beginningWhitespaces) {
                $this->addError($lineCount, 'Expected indention style "tab" but found "spaces"');
                $lineValid = false;
            }
            if ('space' === $this->style && str_contains($whitespaces, "\t")) {
                $this->addError($lineCount, 'Expected indention style "space" but found "tabs"');
                $lineValid = false;
            }

            if ($lineValid && 'space' === $this->style) {
                $tooMuchSpaces = strlen($whitespaces) % $this->size;
                if ($this->strict && $tooMuchSpaces > 0) {
                    $expected = strlen($whitespaces) - $tooMuchSpaces;
                    $actual = $expected + $tooMuchSpaces;
                    $this->addError($lineCount, 'Expected %d spaces, found %d', $expected, $actual);
                }
            }
        }
    }

    public function fixContent(string $content): string
    {
        $lineEnding = LineEndingUtility::detectLineEnding($content, false) ?: "\n";
        /** @var array|string[] $lines */
        $lines = explode($lineEnding, $content);
        foreach ($lines as $no => $line) {
            $beginningWhitespaces = (string)preg_replace('/^([\t\s].*?)\S.*/', '$1', $line);

            if (empty($line) || $beginningWhitespaces === $line) {
                continue;
            }

            // fixed mixed spaces/tabs
            if ('tab' === $this->style) {
                $whitespaces = str_replace(str_repeat(' ', (int)$this->size), "\t", $beginningWhitespaces);
            } else {
                $whitespaces = str_replace("\t", str_repeat(' ', (int)$this->size), $beginningWhitespaces);
            }

            if ('space' === $this->style && $this->strict) {
                $tooMuchSpaces = strlen($whitespaces) % $this->size;
                if ($tooMuchSpaces > 0) {
                    $expected = strlen($whitespaces) - $tooMuchSpaces;
                    $whitespaces = str_repeat(' ', $expected);
                }
            }
            // Update line
            $lines[$no] = $whitespaces . substr($line, strlen($beginningWhitespaces));
        }

        return implode($lineEnding, $lines);
    }
}
