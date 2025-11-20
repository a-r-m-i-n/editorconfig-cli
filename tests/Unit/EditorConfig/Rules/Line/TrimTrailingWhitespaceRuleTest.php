<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\Line;

use Armin\EditorconfigCli\EditorConfig\Rules\Line\TrimTrailingWhitespaceRule;
use PHPUnit\Framework\TestCase;

class TrimTrailingWhitespaceRuleTest extends TestCase
{
    public function testDetectTrailingWhitespacesCorrectly(): void
    {
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Non Trailing\n Non Trailing\nNon Trailing\n");
        $this->assertTrue($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing \n Trailing  \nTrailing\t\nNon Trailing\n");
        $this->assertFalse($subject->isValid());
    }

    public function testFixingTrailingWhitespacesWorks(): void
    {
        $wrongText = "Trailing \n Trailing  \nTrailing\t\nNon Trailing\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $wrongText);
        $result = $subject->fixContent($wrongText);
        $this->assertSame("Trailing\n Trailing\nTrailing\nNon Trailing\n", $result);
    }

    public function testDoNotTouchCorrectTexts(): void
    {
        $correctText = "Non Trailing\n Non Trailing\nNon Trailing\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $correctText);
        $result = $subject->fixContent($correctText);
        $this->assertSame($correctText, $result);
    }
}
