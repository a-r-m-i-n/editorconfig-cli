<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\Line;

use Armin\EditorconfigCli\EditorConfig\Rules\File\EndOfLineRule;
use Armin\EditorconfigCli\EditorConfig\Rules\Line\IndentionRule;
use PHPUnit\Framework\TestCase;

class IndentionRuleTest extends TestCase
{

    public function testDetectWrongIndentionsCorrectly()
    {
        $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n    Non Trailing\n    Non Trailing\n", 'space', 4);
        self::assertTrue($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n\tNon Trailing\n    Non Trailing\n", 'space', 4);
        self::assertFalse($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "\tNon Trailing\n\tNon Trailing\n\tNon Trailing\n", 'tab', 4);
        self::assertTrue($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "\tNon Trailing\n\tNon Trailing\n    Non Trailing\n", 'tab', 4);
        self::assertFalse($subject->isValid());
    }

    public function testDetectWrongStrictModeIndentionsCorrectly()
    {
        $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n    Non Trailing\n    Non Trailing\n", 'space', 4, true);
        self::assertTrue($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n     Non Trailing\n    Non Trailing\n", 'space', 4, true);
        self::assertFalse($subject->isValid());
    }

    public function testDetectWrongIndentionsCorrectlyWhenUppercase()
    {
        $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n    Non Trailing\n    Non Trailing\n", 'SPACE', 4);
        self::assertTrue($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n\tNon Trailing\n    Non Trailing\n", 'SPACE', 4);
        self::assertFalse($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "\tNon Trailing\n\tNon Trailing\n\tNon Trailing\n", 'TAB', 4);
        self::assertTrue($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "\tNon Trailing\n\tNon Trailing\n    Non Trailing\n", 'TAB', 4);
        self::assertFalse($subject->isValid());
    }

    public function testInvalidIndentionStyleConfigThrowsException()
    {
        self::expectException(\InvalidArgumentException::class);
        new IndentionRule('dummy/path/file.txt', "", 'INVALID', 4);
    }

    public function testFixingIndentionWorks()
    {
        $nonStrictText = "    Non Trailing1\n     Non Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $nonStrictText, 'space', 4);
        $result = $subject->fixContent($nonStrictText);
        self::assertSame($nonStrictText, $result, $result);

        $correText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $wrongText = "    Non Trailing1\n\tNon Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 4);
        $result = $subject->fixContent($wrongText);
        self::assertSame($correText, $result, $result);

        $correText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $wrongText = "\tNon Trailing1\n\tNon Trailing2\n\tNon Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 4);
        $result = $subject->fixContent($wrongText);
        self::assertSame($correText, $result, $result);

        $correText = "\tNon Trailing1\n\tNon Trailing2\n\tNon Trailing3\n";
        $wrongText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'tab', 4);
        $result = $subject->fixContent($wrongText);
        self::assertSame($correText, $result, $result);

        $correText = "\tNon Trailing1\n\tNon Trailing2\n\tNon Trailing3\n";
        $wrongText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'tab', 4);
        $result = $subject->fixContent($wrongText);
        self::assertSame($correText, $result, $result);
    }

    public function testFixingIndentionInStrictModeWorks()
    {
        $correText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $wrongText = "    Non Trailing1\n     Non Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 4, true);
        $result = $subject->fixContent($wrongText);
        self::assertSame($correText, $result, $result);

        $correText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $wrongText = "    Non Trailing1\n\tNon Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 4, true);
        $result = $subject->fixContent($wrongText);
        self::assertSame($correText, $result, $result);

        $correText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $wrongText = "\tNon Trailing1\n\tNon Trailing2\n\tNon Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 4, true);
        $result = $subject->fixContent($wrongText);
        self::assertSame($correText, $result, $result);

        $correText = "\tNon Trailing1\n\tNon Trailing2\n\tNon Trailing3\n";
        $wrongText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'tab', 4, true);
        $result = $subject->fixContent($wrongText);
        self::assertSame($correText, $result, $result);
    }
}
