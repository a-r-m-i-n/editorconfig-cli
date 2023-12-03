<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules\Line;

use Armin\EditorconfigCli\EditorConfig\Rules\Rule;
use Armin\EditorconfigCli\EditorConfig\Rules\UnfixableException;
use Armin\EditorconfigCli\EditorConfig\Utility\LineEndingUtility;

class MaxLineLengthRule extends Rule
{
    /**
     * @var int|null
     */
    private $maxLineLength;

    public function __construct(string $filePath, string $fileContent = null, int $maxLineLength = null)
    {
        $this->maxLineLength = $maxLineLength;
        parent::__construct($filePath, $fileContent);
    }

    protected function validate(string $content): void
    {
        $lineEnding = LineEndingUtility::detectLineEnding($content, false);
        if (empty($lineEnding)) {
            $lineEnding = "\n";
        }
        /** @var array|string[] $lines */
        $lines = explode($lineEnding, $content);

        $lineCount = 0;
        foreach ($lines as $line) {
            ++$lineCount;
            $lineLength = strlen($line);
            if ($lineLength > $this->maxLineLength) {
                $this->addError($lineCount, 'Max line length (%d chars) exceeded by %d chars', $this->maxLineLength, $lineLength);
            }
        }
    }

    public function fixContent(string $content): string
    {
        throw new UnfixableException(sprintf('Automatic fix of exceeded max line length is not possible for file "%s"', $this->getFilePath()), 1620998670);
    }
}
