<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\File\CharsetRule;
use Armin\EditorconfigCli\EditorConfig\Rules\UnfixableException;
use PHPUnit\Framework\TestCase;

class CharsetRuleTest extends TestCase
{
    public function testDetectsCharsetsCorrectly(): void
    {
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('latin1'), 'latin1');
        $this->assertTrue($subject->isValid(), $subject->getErrorsAsText());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8'), 'utf-8');
        $this->assertTrue($subject->isValid(), $subject->getErrorsAsText());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8-bom'), 'utf-8-bom');
        $this->assertTrue($subject->isValid(), $subject->getErrorsAsText());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16be'), 'utf-16be');
        $this->assertTrue($subject->isValid(), $subject->getErrorsAsText());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16le'), 'utf-16le');
        $this->assertTrue($subject->isValid(), $subject->getErrorsAsText());

        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16be'), 'utf-16le');
        $this->assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16le'), 'utf-16be');
        $this->assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8'), 'utf-16le');
        $this->assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8'), 'utf-16be');
        $this->assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('latin1'), 'utf-16be');
        $this->assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('latin1'), 'utf-16le');
        $this->assertFalse($subject->isValid());

        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16be'), 'utf-8');
        $this->assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16le'), 'utf-8');
        $this->assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('latin1'), 'utf-8');
        $this->assertFalse($subject->isValid());

        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8'), 'latin1');
        $this->assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16be'), 'latin1');
        $this->assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16le'), 'latin1');
        $this->assertFalse($subject->isValid());

        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8-bom'), 'utf-8');
        $this->assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8'), 'utf-8-bom');
        $this->assertFalse($subject->isValid());
    }

    public function testFixingCharsetNotSupported(): void
    {
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8-bom'), 'utf-8');

        $this->expectException(UnfixableException::class);
        $subject->fixContent('');
    }

    private function loadText(string $name): string
    {
        return (string)file_get_contents(__DIR__ . '/Data/CharsetRuleTest/' . $name . '.txt');
    }
}
