<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules;

abstract class Rule implements RuleInterface
{
    public const CHARSET = 'charset';
    public const END_OF_LINE = 'end_of_line';
    public const INDENT_SIZE = 'indent_size';
    public const INDENT_STYLE = 'indent_style';
    public const TAB_WIDTH = 'tab_width';
    public const INSERT_FINAL_NEWLINE = 'insert_final_newline';
    public const MAX_LINE_LENGTH = 'max_line_length';
    public const TRIM_TRAILING_WHITESPACE = 'trim_trailing_whitespace';

    /**
     * @var RuleError[]
     */
    protected array $errors = [];

    /**
     * @return string[]
     */
    public static function getDefinitions(): array
    {
        return [
            self::CHARSET,
            self::END_OF_LINE,
            self::INDENT_SIZE,
            self::INDENT_STYLE,
            self::TAB_WIDTH,
            self::INSERT_FINAL_NEWLINE,
            self::MAX_LINE_LENGTH,
            self::TRIM_TRAILING_WHITESPACE,
        ];
    }

    public function __construct(
        protected string $filePath,
        ?string $fileContent = null
    ) {
        $this->validate($fileContent ?? (string)file_get_contents($this->filePath));
    }

    abstract protected function validate(string $content): void;

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return RuleError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Only used in UnitTests.
     *
     * @codeCoverageIgnore
     */
    public function getErrorsAsText(): string
    {
        $errors = [];
        foreach ($this->errors as $error) {
            $errors[] = (string)$error;
        }

        return implode("\n", $errors);
    }

    /**
     * @param int|string|null ...$arguments
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
