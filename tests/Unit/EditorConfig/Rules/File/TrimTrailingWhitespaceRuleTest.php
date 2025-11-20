<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\File\TrimTrailingWhitespaceRule;
use PHPUnit\Framework\TestCase;

class TrimTrailingWhitespaceRuleTest extends TestCase
{
    public function testDetectTrailingWhitespacesCorrectly(): void
    {
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing\n", true);
        $this->assertTrue($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', 'Trailing', false);
        $this->assertTrue($subject->isValid());

        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing\n\n", false);
        $this->assertFalse($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing\n\n", true);
        $this->assertFalse($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', 'Trailing   ', false);
        $this->assertFalse($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing   \n\n", true);
        $this->assertFalse($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', '', true);
        $this->assertTrue($subject->isValid());
    }

    public function testFixingTrailingWhitespacesWorks(): void
    {
        $wrongText = "Trailing\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $wrongText, false);
        $result = $subject->fixContent($wrongText);
        $this->assertSame('Trailing', $result);

        $wrongText = "\n\nTrailing    \n\n\n\n\n\n\n\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $wrongText, true);
        $result = $subject->fixContent($wrongText);
        $this->assertSame("\n\nTrailing\n", $result);
    }

    public function testDoNotTouchCorrectTexts(): void
    {
        $correctText = "Trailing\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $correctText, true);
        $result = $subject->fixContent($correctText);
        $this->assertSame($correctText, $result);

        $correctText = 'Trailing';
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $correctText, false);
        $result = $subject->fixContent($correctText);
        $this->assertSame($correctText, $result);

        $correctText = "\n\n\n\nTrailing\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $correctText, true);
        $result = $subject->fixContent($correctText);
        $this->assertSame($correctText, $result);
    }
}
