<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules;

use Armin\EditorconfigCli\EditorConfig\Rules\File\CharsetRule;
use Armin\EditorconfigCli\EditorConfig\Rules\File\EndOfLineRule;
use Armin\EditorconfigCli\EditorConfig\Rules\File\InsertFinalNewLineRule;
use Armin\EditorconfigCli\EditorConfig\Rules\File\TrimTrailingWhitespaceRule;
use Armin\EditorconfigCli\EditorConfig\Rules\Line\IndentionRule;
use Armin\EditorconfigCli\EditorConfig\Rules\Line\MaxLineLengthRule;
use Armin\EditorconfigCli\EditorConfig\Utility\MimeTypeUtility;
use Idiosyncratic\EditorConfig\Declaration\Charset;
use Idiosyncratic\EditorConfig\Declaration\Declaration;
use Idiosyncratic\EditorConfig\Declaration\TrimTrailingWhitespace;
use Symfony\Component\Finder\SplFileInfo;

class Validator
{
    /**
     * @var array|Declaration[]
     */
    private array $editorConfig;

    /**
     * @var array|string[]
     */
    private array $skippingRules;

    public function createValidatedFileResult(SplFileInfo $file, array $editorConfig, bool $strictMode = false, array $skippingRules = []): FileResult
    {
        $this->editorConfig = $editorConfig;
        $this->skippingRules = $skippingRules;

        $filePath = (string)$file->getRealPath();

        if (empty($filePath)) {
            throw (new FileUnavailableException())->setUnavailableFile($file);
        }

        $rules = [];

        if (!MimeTypeUtility::isCommonTextType($filePath) && (MimeTypeUtility::isCommonBinaryType($filePath) || MimeTypeUtility::isBinaryFileType($filePath))) {
            return new FileResult($filePath, [], true); // Skip non-ascii files
        }

        // Line rules
        $style = $size = $width = null;

        if ($this->hasRuleSet(Rule::INDENT_STYLE)) {
            $style = $editorConfig[Rule::INDENT_STYLE]->getStringValue();
        }
        if ($this->hasRuleSet(Rule::INDENT_SIZE)) {
            $size = $editorConfig[Rule::INDENT_SIZE]->getValue();
        }
        if ($this->hasRuleSet(Rule::TAB_WIDTH)) {
            $width = $editorConfig[Rule::TAB_WIDTH]->getValue();
        }
        $size ??= $width;
        if (!$size) {
            $size = 4;
        }
        if ($style) {
            $rules[] = new IndentionRule($filePath, $file->getContents(), $style, $size, $strictMode);
        }

        if (isset($editorConfig['trim_trailing_whitespace']) && $editorConfig['trim_trailing_whitespace'] instanceof TrimTrailingWhitespace && $editorConfig['trim_trailing_whitespace']->getValue()) {
            $rules[] = new Line\TrimTrailingWhitespaceRule($filePath, $file->getContents());
        }

        // File rules
        if (isset($editorConfig['charset']) && $editorConfig['charset'] instanceof Charset) {
            $rules[] = new CharsetRule($filePath, $file->getContents(), strtolower($editorConfig['charset']->getStringValue()));
        }

        $eofRule = null;
        if ($this->hasRuleSet(Rule::END_OF_LINE)) {
            $rules[] = $eofRule = new EndOfLineRule($filePath, $file->getContents(), $editorConfig['end_of_line']->getStringValue());
        }

        $insertFinalNewLine = null;
        if ($this->hasRuleSet(Rule::INSERT_FINAL_NEWLINE) && $insertFinalNewLine = $editorConfig[Rule::INSERT_FINAL_NEWLINE]->getValue()) {
            $rules[] = new InsertFinalNewLineRule($filePath, $file->getContents(), $eofRule ? $eofRule->getEndOfLine() : null);
        }
        if ($this->hasRuleSet(Rule::TRIM_TRAILING_WHITESPACE)) {
            $rules[] = new TrimTrailingWhitespaceRule($filePath, $file->getContents(), $insertFinalNewLine ?? false);
        }

        if ($this->hasRuleSet(Rule::MAX_LINE_LENGTH) && 'off' !== $editorConfig['max_line_length']->getValue()) {
            $maxLineLength = (int)$editorConfig['max_line_length']->getValue();
            if ($maxLineLength > 0) {
                $rules[] = new MaxLineLengthRule($filePath, $file->getContents(), $maxLineLength);
            }
        }

        return new FileResult($filePath, $rules);
    }

    /**
     * @param string $ruleName see \Armin\EditorconfigCli\EditorConfig\Rules\Rule class constants
     */
    private function hasRuleSet(string $ruleName): bool
    {
        return !in_array($ruleName, $this->skippingRules, true)
            && isset($this->editorConfig[$ruleName])
            && $this->editorConfig[$ruleName] instanceof Declaration
            && ($this->editorConfig[$ruleName]->getValue() || $this->editorConfig[$ruleName]->getStringValue());
    }
}
