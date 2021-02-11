<?php

declare(strict_types = 1);

namespace FGTCLB\EditorConfig\EditorConfig\Rules;

abstract class AbstractRule implements RuleInterface
{
    protected string $filePath;

    /**
     * @var array|RuleError[]
     */
    protected array $errors = [];

    public function __construct(string $filePath, string $fileContent = null)
    {
        $this->filePath = $filePath;
        $this->validate($fileContent ?? (string)file_get_contents($this->filePath));
    }

    abstract protected function validate(string $content): bool;

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorsAsText(): string
    {
        $errors = [];
        foreach ($this->errors as $error) {
            $errors[] = (string)$error;
        }

        return implode("\n", $errors);
    }

    /**
     * @param mixed ...$arguments
     */
    public function addError(?int $line, string $message, ...$arguments): void
    {
        $this->errors[] = new RuleError(vsprintf($message, $arguments), $line);
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }
}
