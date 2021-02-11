<?php

declare(strict_types = 1);

namespace FGTCLB\EditorConfig\EditorConfig\Rules\File;

use FGTCLB\EditorConfig\EditorConfig\Rules\AbstractRule;
use FGTCLB\EditorConfig\EditorConfig\Utility\FileEncodingUtility;

class CharsetRule extends AbstractRule
{
    private string $expectedEncoding;

    public function __construct(string $filePath, string $fileContent, string $expectedEncoding)
    {
        $this->expectedEncoding = strtolower($expectedEncoding);
        parent::__construct($filePath, $fileContent);
    }

    protected function validate(string $content): bool
    {
        $actualEncoding = FileEncodingUtility::detect($content);
        $result = $this->expectedEncoding === $actualEncoding;
        if (!$result) {
            $this->addError(
                null,
                'Given encoding does not match! Expected: "%s", Given: "%s"',
                $this->expectedEncoding,
                $actualEncoding
            );
        }

        return $result;
    }

    public function fixContent(string $content): string
    {
        $toEncoding = $this->expectedEncoding;
        if ('latin1' === $toEncoding) {
            $toEncoding = 'iso-8859-1';
        }
        $fromEncoding = (string)FileEncodingUtility::detect($content);
        if ('latin1' === $fromEncoding) {
            $fromEncoding = 'iso-8859-1';
        }

        $bom = '';
        if ('utf-8-bom' === $this->expectedEncoding) {
            $bom = FileEncodingUtility::getUTF8ByteOrderMark();
            $toEncoding = 'utf-8';
        }

        return $bom . iconv($fromEncoding, $toEncoding, $content);
    }
}
