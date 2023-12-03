<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\Rule;
use Armin\EditorconfigCli\EditorConfig\Rules\UnfixableException;
use Armin\EditorconfigCli\EditorConfig\Utility\FileEncodingUtility;

class CharsetRule extends Rule
{
    private string $expectedEncoding;

    public function __construct(string $filePath, string $fileContent, string $expectedEncoding)
    {
        $this->expectedEncoding = strtolower($expectedEncoding);
        parent::__construct($filePath, $fileContent);
    }

    protected function validate(string $content): void
    {
        $actualEncoding = FileEncodingUtility::detect($content);
        if ($this->expectedEncoding !== $actualEncoding) {
            $this->addError(
                null,
                'This file has invalid encoding given! Expected: "%s", Given: "%s"',
                $this->expectedEncoding,
                $actualEncoding
            );
        }
    }

    /**
     * @throws UnfixableException
     */
    public function fixContent(string $content): string
    {
        throw new UnfixableException(sprintf('Automatic fix of wrong charset is not possible for file "%s"', $this->getFilePath()), 1620996364);
    }
}
