<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules;

use Armin\EditorconfigCli\EditorConfig\Rules\File\CharsetRule;
use Armin\EditorconfigCli\EditorConfig\Rules\File\EndOfLineRule;
use Armin\EditorconfigCli\EditorConfig\Rules\File\InsertFinalNewLineRule;
use Armin\EditorconfigCli\EditorConfig\Rules\File\TrimTrailingWhitespaceRule;
use Armin\EditorconfigCli\EditorConfig\Rules\Line\IndentionRule;
use Armin\EditorconfigCli\EditorConfig\Rules\Line\MaxLineLengthRule;
use Idiosyncratic\EditorConfig\Declaration\Charset;
use Idiosyncratic\EditorConfig\Declaration\EndOfLine;
use Idiosyncratic\EditorConfig\Declaration\IndentSize;
use Idiosyncratic\EditorConfig\Declaration\IndentStyle;
use Idiosyncratic\EditorConfig\Declaration\InsertFinalNewline;
use Idiosyncratic\EditorConfig\Declaration\MaxLineLength;
use Idiosyncratic\EditorConfig\Declaration\TabWidth;
use Idiosyncratic\EditorConfig\Declaration\TrimTrailingWhitespace;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Mime\MimeTypes;

class Validator
{
    public function createValidatedFileResult(SplFileInfo $file, array $editorConfig, bool $strictMode = false): FileResult
    {
        $filePath = (string)$file->getRealPath();
        $rules = [];

        $mime = new MimeTypes();
        $mimeType = (string)$mime->guessMimeType($filePath);
        if (0 !== strpos($mimeType, 'text/')) {
            return new FileResult($filePath, [], true); // Skip non-ascii files
        }

        // Line rules
        $style = $size = $width = null;
        if (isset($editorConfig['indent_style']) && $editorConfig['indent_style'] instanceof IndentStyle) {
            $style = $editorConfig['indent_style']->getStringValue();
        }
        if (isset($editorConfig['indent_size']) && $editorConfig['indent_size'] instanceof IndentSize) {
            $size = $editorConfig['indent_size']->getValue();
        }
        if (isset($editorConfig['tab_width']) && $editorConfig['tab_width'] instanceof TabWidth) {
            $width = $editorConfig['tab_width']->getValue();
        }
        $size = $size ?? $width;
        if (!$size) {
            $size = 4;
        }
        if ($style && $size) {
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
        if (isset($editorConfig['end_of_line']) && $editorConfig['end_of_line'] instanceof EndOfLine) {
            $rules[] = $eofRule = new EndOfLineRule($filePath, $file->getContents(), $editorConfig['end_of_line']->getStringValue());
        }

        if (isset($editorConfig['insert_final_newline']) && $editorConfig['insert_final_newline'] instanceof InsertFinalNewline && $insertFinalNewLine = $editorConfig['insert_final_newline']->getValue()) {
            $rules[] = new InsertFinalNewLineRule($filePath, $file->getContents(), $eofRule ? $eofRule->getEndOfLine() : null);
        }
        if (isset($editorConfig['trim_trailing_whitespace']) && $editorConfig['trim_trailing_whitespace'] instanceof TrimTrailingWhitespace && $editorConfig['trim_trailing_whitespace']->getValue()) {
            $rules[] = new TrimTrailingWhitespaceRule($filePath, $file->getContents(), $insertFinalNewLine ?? false);
        }

        if (isset($editorConfig['max_line_length']) && $editorConfig['max_line_length'] instanceof MaxLineLength && $editorConfig['max_line_length']->getValue() && 'off' !== $editorConfig['max_line_length']->getValue()) {
            $maxLineLength = (int)$editorConfig['max_line_length']->getValue();
            if ($maxLineLength > 0) {
                $rules[] = new MaxLineLengthRule($filePath, $file->getContents(), $maxLineLength);
            }
        }

        return new FileResult($filePath, $rules);
    }
}
