<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules;

class FileResult
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var array|AbstractRule[]
     */
    private $rules;

    /**
     * @var bool
     */
    private $isBinary;

    public function __construct(string $filePath, array $rules, bool $isBinary = false)
    {
        $this->rules = $rules;
        $this->filePath = $filePath;
        $this->isBinary = $isBinary;
        foreach ($this->rules as $rule) {
            if ($rule->getFilePath() !== $this->filePath) {
                throw new \InvalidArgumentException(sprintf('Given rules in FileResult must all be related to the same file! Rule expects file "%s" but file "%s" is given.', $this->filePath, $rule->getFilePath()));
            }
        }
    }

    public function hasDeclarations(): bool
    {
        return !empty($this->rules);
    }

    public function isBinary(): bool
    {
        return $this->isBinary;
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
        if (!$this->hasDeclarations()) {
            return;
        }
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
