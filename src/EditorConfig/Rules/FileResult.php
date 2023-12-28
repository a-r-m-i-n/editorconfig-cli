<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules;

class FileResult
{
    /**
     * @var array|UnfixableException[]
     */
    private array $unfixableExceptions = [];

    /**
     * @param Rule[] $rules
     */
    public function __construct(
        private readonly string $filePath,
        private readonly array $rules,
        private readonly bool $isBinary = false
    ) {
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
        /** @var Rule $rule */
        foreach ($this->rules as $rule) {
            array_push($errors, ...$rule->getErrors());
        }

        uasort($errors, static fn (RuleError $a, RuleError $b): int => $a->getLine() > $b->getLine() ? 1 : -1);

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

    public function applyFixes(): void
    {
        $content = (string)file_get_contents($this->getFilePath());
        foreach ($this->rules as $rule) {
            if (!$rule->isValid()) {
                try {
                    $content = $rule->fixContent($content);
                } catch (UnfixableException $e) {
                    $this->unfixableExceptions[] = $e;
                }
            }
        }
        $status = file_put_contents($this->getFilePath(), $content);
        if (!$status) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException(sprintf('Unable to update file "%s"!', $this->getFilePath()));
            // @codeCoverageIgnoreEnd
        }
    }

    public function hasUnfixableExceptions(): bool
    {
        return count($this->getUnfixableExceptions()) > 0;
    }

    /**
     * @return UnfixableException[]
     */
    public function getUnfixableExceptions(): array
    {
        return $this->unfixableExceptions;
    }
}
