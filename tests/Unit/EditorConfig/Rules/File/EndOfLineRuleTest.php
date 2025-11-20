<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\File\EndOfLineRule;
use PHPUnit\Framework\TestCase;

class EndOfLineRuleTest extends TestCase
{
    public function testDetectWrongLineEndingsCorrectly(): void
    {
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\nist\nLF\n", 'lf');
        $this->assertTrue($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\nist\nLF\n", 'cr');
        $this->assertFalse($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\nist\nLF\n", 'crlf');
        $this->assertFalse($subject->isValid());

        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\rist\rLF\r", 'cr');
        $this->assertTrue($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\rist\rLF\r", 'lf');
        $this->assertFalse($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\rist\rLF\r", 'crlf');
        $this->assertFalse($subject->isValid());

        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\r\nist\r\nLF\r\n", 'crlf');
        $this->assertTrue($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\r\nist\r\nLF\r\n", 'lf');
        $this->assertFalse($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\r\nist\r\nLF\r\n", 'cr');
        $this->assertFalse($subject->isValid());
    }

    public function testDetectWrongLineEndingsCorrectlyWhenUppercase(): void
    {
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\nist\nLF\n", 'LF');
        $this->assertTrue($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\nist\nLF\n", 'CR');
        $this->assertFalse($subject->isValid());
        $subject = new EndOfLineRule('dummy/path/file.txt', "Test\nist\nLF\n", 'CRLF');
        $this->assertFalse($subject->isValid());
    }

    public function testFixWrongLineEndingsCorrectlyToCR(): void
    {
        $wrongText = "Test\nist\nLF\n";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'cr');
        $result = $subject->fixContent($wrongText);
        $this->assertStringContainsString("\r", $result);
        $this->assertStringNotContainsString("\n", $result);

        $wrongText = "Test\r\nist\r\nLF\r\n";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'cr');
        $result = $subject->fixContent($wrongText);
        $this->assertStringContainsString("\r", $result);
        $this->assertStringNotContainsString("\n", $result);
    }

    public function testFixWrongLineEndingsCorrectlyToLF(): void
    {
        $wrongText = "Test\rist\rLF\r";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'lf');
        $result = $subject->fixContent($wrongText);
        $this->assertStringContainsString("\n", $result);
        $this->assertStringNotContainsString("\r", $result);

        $wrongText = "Test\r\nist\r\nLF\r\n";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'lf');
        $result = $subject->fixContent($wrongText);
        $this->assertStringContainsString("\n", $result);
        $this->assertStringNotContainsString("\r", $result);
    }

    public function testFixWrongLineEndingsCorrectlyToCRLF(): void
    {
        $wrongText = "Test\nist\nLF\n";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'crlf');
        $result = $subject->fixContent($wrongText);
        $this->assertStringContainsString("\r\n", $result);

        $wrongText = "Test\rist\rLF\r";
        $subject = new EndOfLineRule('dummy/path/file.txt', $wrongText, 'crlf');
        $result = $subject->fixContent($wrongText);
        $this->assertStringContainsString("\r\n", $result);
    }
}
