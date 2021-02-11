<?php declare(strict_types = 1);
namespace FGTCLB\EditorConfig\Tests\Unit\EditorConfig\Rules\Line;

use FGTCLB\EditorConfig\EditorConfig\Rules\Line\TrimTrailingWhitespaceRule;
use PHPUnit\Framework\TestCase;

class TrimTrailingWhitespaceRuleTest extends TestCase
{

    public function testDetectTrailingWhitespacesCorrectly()
    {
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Non Trailing\n Non Trailing\nNon Trailing\n");
        self::assertTrue($subject->isValid());
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', "Trailing \n Trailing  \nTrailing\t\nNon Trailing\n");
        self::assertFalse($subject->isValid());
    }

    public function testFixingTrailingWhitespacesWorks()
    {
        $wrongText = "Trailing \n Trailing  \nTrailing\t\nNon Trailing\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $wrongText);
        $result = $subject->fixContent($wrongText);
        self::assertSame("Trailing\n Trailing\nTrailing\nNon Trailing\n", $result);
    }

    public function testDoNotTouchCorrectTexts()
    {
        $correctText = "Non Trailing\n Non Trailing\nNon Trailing\n";
        $subject = new TrimTrailingWhitespaceRule('dummy/path/file.txt', $correctText);
        $result = $subject->fixContent($correctText);
        self::assertSame($correctText, $result);
    }
}
