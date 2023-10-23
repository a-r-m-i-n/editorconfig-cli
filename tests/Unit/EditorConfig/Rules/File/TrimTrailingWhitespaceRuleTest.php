<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\File\TrimTrailingWhitespaceRule;
use PHPUnit\Framework\TestCase;

class TrimTrailingWhitespaceRuleTest extends TestCase
{

    public function testDetectTrailingWhitespacesCorrectly()
    {
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing\n", true);
        self::assertTrue($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing", false);
        self::assertTrue($subject->isValid());

        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing\n\n", false);
        self::assertFalse($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing\n\n", true);
        self::assertFalse($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing   ", false);
        self::assertFalse($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing   \n\n", true);
        self::assertFalse($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "", true);
        self::assertTrue($subject->isValid());
    }

    public function testFixingTrailingWhitespacesWorks()
    {
        $wrongText = "Trailing\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $wrongText, false);
        $result = $subject->fixContent($wrongText);
        self::assertSame('Trailing', $result);

        $wrongText = "\n\nTrailing    \n\n\n\n\n\n\n\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $wrongText, true);
        $result = $subject->fixContent($wrongText);
        self::assertSame("\n\nTrailing\n", $result);
    }

    public function testDoNotTouchCorrectTexts()
    {
        $correctText = "Trailing\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $correctText, true);
        $result = $subject->fixContent($correctText);
        self::assertSame($correctText, $result);

        $correctText = "Trailing";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $correctText, false);
        $result = $subject->fixContent($correctText);
        self::assertSame($correctText, $result);

        $correctText = "\n\n\n\nTrailing\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $correctText, true);
        $result = $subject->fixContent($correctText);
        self::assertSame($correctText, $result);
    }

    public function testDoNotTouchWhenEmpty()
    {
        $correctText = "";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $correctText, true);
        $result = $subject->fixContent($correctText);
        self::assertSame($correctText, $result);
    }

}
