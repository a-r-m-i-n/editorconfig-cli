<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\Line;

use Armin\EditorconfigCli\EditorConfig\Rules\Rule;
use Armin\EditorconfigCli\EditorConfig\Rules\UnfixableException;
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

        parent::__construct($filePath, $fileContent);
    }

    protected function validate(string $content): void
    {
        $lineEnding = LineEndingUtility::detectLineEnding($content, false) ?: "\n";
        /** @var array|string[] $lines */
        $lines = explode($lineEnding, $content);

        $lineCount = 0;
        foreach ($lines as $line) {
            ++$lineCount;
            $lineValid = true;
            $beginningWhitespaces = preg_replace('/^(\s.*?)\S.*/', '$1', $line);

            if (empty($line) || $beginningWhitespaces === $line || null === $beginningWhitespaces) {
                continue;
            }

            if ('tab' === $this->style && str_starts_with($beginningWhitespaces, ' ')) {
                $this->addError($lineCount, 'Expected indention style "tab" but found "spaces"');
                $lineValid = false;
            }
            if ('space' === $this->style && str_starts_with($beginningWhitespaces, "\t")) {
                $this->addError($lineCount, 'Expected indention style "space" but found "tabs"');
                $lineValid = false;
            }

            if (!$lineValid || !$this->strict) {
                continue;
            }

            // Strict indention checks
            if ('space' === $this->style && (int)$this->size > 0) {
                $tooMuchSpaces = strlen($beginningWhitespaces) % $this->size;
                if ($tooMuchSpaces > 0) {
                    $expectedMin = (int)floor(strlen($beginningWhitespaces) / $this->size) * $this->size;
                    $expectedMax = (int)ceil(strlen($beginningWhitespaces) / $this->size) * $this->size;

                    $actual = $expectedMin + $tooMuchSpaces;
                    $this->addError($lineCount, 'Expected %d or %d spaces, found %d', $expectedMin, $expectedMax, $actual);
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
            $beginningWhitespaces = preg_replace('/^([\t\s].*?)\S.*/', '$1', $line);

            if (empty($line) || $beginningWhitespaces === $line || null === $beginningWhitespaces) {
                continue;
            }

            $whitespaces = '';

            // fixed mixed spaces/tabs
            if ('tab' === $this->style) {
                if (0 === (int)$this->size && str_starts_with($beginningWhitespaces, ' ')) {
                    throw $this->getUnfixableException(1763644380);
                }
                $whitespaces = str_replace(str_repeat(' ', (int)$this->size), "\t", $beginningWhitespaces);

                if (str_starts_with($whitespaces, ' ')) {
                    $whitespaces = preg_replace('/^\s*?(\t.*)/', '$1', $whitespaces) ?: $whitespaces;
                }
                if (!str_contains($whitespaces, "\t")) {
                    $whitespaces = str_replace(' ', '', $whitespaces);
                }
            }

            if ('space' === $this->style) {
                if (0 === (int)$this->size && str_starts_with($beginningWhitespaces, "\t")) {
                    throw $this->getUnfixableException(1763644381);
                }
                $whitespaces = str_replace("\t", str_repeat(' ', (int)$this->size), $beginningWhitespaces);
            }

            if ('space' === $this->style && $this->strict) {
                if (0 === (int)$this->size) {
                    throw $this->getUnfixableException(1763644382);
                }
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

    private function getUnfixableException(int $errorCode): UnfixableException
    {
        return new UnfixableException(sprintf('Automatic fix of line indention is not possible for file "%s", because indent_size is not defined.', $this->getFilePath()), $errorCode);
    }
}
