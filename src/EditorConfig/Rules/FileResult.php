<?php

declare(strict_types = 1);

namespace FGTCLB\EditorConfig\EditorConfig\Rules;

class FileResult
{
    private string $filePath;

    /** @var array|AbstractRule[] */
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
        foreach ($this->rules as $rule) {
            $this->filePath = $rule->getFilePath();
            if ($rule->getFilePath() !== $this->filePath) {
                throw new \InvalidArgumentException(sprintf('Given rules in FileResult must all be related to the same file! Rule expects file "%s" but file "%s" is given.', $this->filePath, $rule->getFilePath()));
            }
        }
    }

    public function isValid(): bool
    {
        foreach ($this->rules as $rule) {
            if (!$rule->isValid()) {
                return false;
            }
        }

        return true;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return array|RuleError[]
     */
    public function getErrors(): array
    {
        $errors = [];
        /** @var AbstractRule $rule */
        foreach ($this->rules as $rule) {
            array_push($errors, ...$rule->getErrors());
        }

        return $errors;
    }

    public function countErrors(): int
    {
        return count($this->getErrors());
    }

    public function getErrorsAsString(): string
    {
        $errors = [];
        foreach ($this->getErrors() as $error) {
            $errors[] = (string)$error;
        }

        return trim(implode("\n", $errors));
    }

    public function __toString(): string
    {
        if ($this->isValid()) {
            return $this->filePath . ' - OK';
        }

        return $this->filePath . ' - ERR: ' . $this->getErrorsAsString();
    }

    public function applyFixes(): void
    {
        $content = (string)file_get_contents($this->getFilePath());
        foreach ($this->rules as $rule) {
            $content = $rule->fixContent($content);
        }
        $status = file_put_contents($this->getFilePath(), $content);
        if (!$status) {
            throw new \RuntimeException(sprintf('Unable to update file "%s"!', $this->getFilePath()));
        }
    }
}
