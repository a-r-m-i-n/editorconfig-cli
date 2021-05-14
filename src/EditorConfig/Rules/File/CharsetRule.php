<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\AbstractRule;
use Armin\EditorconfigCli\EditorConfig\Rules\UnfixableException;
use Armin\EditorconfigCli\EditorConfig\Utility\FileEncodingUtility;

class CharsetRule extends AbstractRule
{
    /**
     * @var string
     */
    private $expectedEncoding;

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

    /**
     * @throws UnfixableException
     */
    public function fixContent(string $content): string
    {
        throw new UnfixableException(sprintf('Automatic fix of wrong charset is not possible for file "%s"', $this->getFilePath()), 1620996364);
    }
}
