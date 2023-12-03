<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\Rule;
use Armin\EditorconfigCli\EditorConfig\Utility\LineEndingUtility;

class EndOfLineRule extends Rule
{
    /**
     * @var string
     */
    private $endOfLine;

    /**
     * @var string
     */
    private $expectedEndOfLine;

    public function __construct(string $filePath, string $fileContent, string $endOfLine)
    {
        $this->endOfLine = strtolower($endOfLine);
        $this->expectedEndOfLine = LineEndingUtility::convertReadableToActualChars($this->endOfLine) ?? '';

        if ('' === $this->expectedEndOfLine) {
            throw new \InvalidArgumentException(sprintf('Unknown end of line value "%s" given in .editorconfig', $endOfLine), 1621325385);
        }

        parent::__construct($filePath, $fileContent);
    }

    public function getEndOfLine(): string
    {
        return $this->expectedEndOfLine;
    }

    protected function validate(string $content): void
    {
        $whitespacesOnly = (string)preg_replace('/[^\r\n]/i', '', $content);

        $actualEndOfLine = substr($whitespacesOnly, 0, 2);
        if (!empty($actualEndOfLine) && "\r\n" !== $actualEndOfLine) {
            $actualEndOfLine = $actualEndOfLine[0]; // first char only
        }
        $result = $this->expectedEndOfLine === $actualEndOfLine || empty($actualEndOfLine);
        if (!$result) {
            $this->addError(
                null,
                'This file has line ending "%s" given, but "%s" is expected',
                LineEndingUtility::convertActualCharToReadable($actualEndOfLine),
                $this->endOfLine
            );
        }
    }

    public function fixContent(string $content): string
    {
        return str_replace(["\r\n", "\r", "\n"], $this->expectedEndOfLine, $content);
    }
}
