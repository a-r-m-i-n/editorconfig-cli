<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\File\EndOfLineRule;
use PHPUnit\Framework\TestCase;

class EndOfLineRuleTest extends TestCase
{

    public function testDetectWrongLineEndingsCorrectly()
    {
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\nist\nLF\n", 'lf');
        self::assertTrue($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\nist\nLF\n", 'cr');
        self::assertFalse($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\nist\nLF\n", 'crlf');
        self::assertFalse($subject->isValid());

        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\rist\rLF\r", 'cr');
        self::assertTrue($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\rist\rLF\r", 'lf');
        self::assertFalse($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\rist\rLF\r", 'crlf');
        self::assertFalse($subject->isValid());

        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\r\nist\r\nLF\r\n", 'crlf');
        self::assertTrue($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\r\nist\r\nLF\r\n", 'lf');
        self::assertFalse($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\r\nist\r\nLF\r\n", 'cr');
        self::assertFalse($subject->isValid());
    }

    public function testFixWrongLineEndingsCorrectlyToCR()
    {
        $wrongText = "Test\nist\nLF\n";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'cr');
        $result = $subject->fixContent($wrongText);
        self::assertStringContainsString("\r", $result);
        self::assertStringNotContainsString("\n", $result);

        $wrongText = "Test\r\nist\r\nLF\r\n";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'cr');
        $result = $subject->fixContent($wrongText);
        self::assertStringContainsString("\r", $result);
        self::assertStringNotContainsString("\n", $result);
    }

    public function testFixWrongLineEndingsCorrectlyToLF()
    {
        $wrongText = "Test\rist\rLF\r";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'lf');
        $result = $subject->fixContent($wrongText);
        self::assertStringContainsString("\n", $result);
        self::assertStringNotContainsString("\r", $result);

        $wrongText = "Test\r\nist\r\nLF\r\n";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'lf');
        $result = $subject->fixContent($wrongText);
        self::assertStringContainsString("\n", $result);
        self::assertStringNotContainsString("\r", $result);
    }

    public function testFixWrongLineEndingsCorrectlyToCRLF()
    {
        $wrongText = "Test\nist\nLF\n";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'crlf');
        $result = $subject->fixContent($wrongText);
        self::assertStringContainsString("\r\n", $result);

        $wrongText = "Test\rist\rLF\r";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'crlf');
        $result = $subject->fixContent($wrongText);
        self::assertStringContainsString("\r\n", $result);
    }
}
