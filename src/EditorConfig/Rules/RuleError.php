<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules;

class RuleError
{
    private string $message;

    private ?int $line;

    public function __construct(string $message, ?int $line = null)
    {
        $this->message = $message;
        $this->line = $line;
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
