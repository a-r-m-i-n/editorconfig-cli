<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules;

readonly class RuleError implements \Stringable
{
    public function __construct(
        private string $message,
        private ?int $line = null
    ) {
    }

    public function getLine(): int
    {
        return $this->line ?? 0;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function __toString(): string
    {
        $errorString = '';
        if (!empty($this->getLine())) {
            $errorString = 'Line ' . $this->getLine() . ': ';
        }
        $errorString .= $this->getMessage();

        return $errorString;
    }
}
