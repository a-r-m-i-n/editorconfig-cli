<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\File\CharsetRule;
use PHPUnit\Framework\TestCase;

class CharsetRuleTest extends TestCase
{

    public function testDetectsCharsetsCorrectly()
    {
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('latin1'), 'latin1');
        self::assertTrue($subject->isValid(), $subject->getErrorsAsText());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8'), 'utf-8');
        self::assertTrue($subject->isValid(), $subject->getErrorsAsText());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8-bom'), 'utf-8-bom');
        self::assertTrue($subject->isValid(), $subject->getErrorsAsText());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16be'), 'utf-16be');
        self::assertTrue($subject->isValid(), $subject->getErrorsAsText());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16le'), 'utf-16le');
        self::assertTrue($subject->isValid(), $subject->getErrorsAsText());


        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16be'), 'utf-16le');
        self::assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16le'), 'utf-16be');
        self::assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8'), 'utf-16le');
        self::assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8'), 'utf-16be');
        self::assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('latin1'), 'utf-16be');
        self::assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('latin1'), 'utf-16le');
        self::assertFalse($subject->isValid());

        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16be'), 'utf-8');
        self::assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16le'), 'utf-8');
        self::assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('latin1'), 'utf-8');
        self::assertFalse($subject->isValid());

        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8'), 'latin1');
        self::assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16be'), 'latin1');
        self::assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-16le'), 'latin1');
        self::assertFalse($subject->isValid());

        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8-bom'), 'utf-8');
        self::assertFalse($subject->isValid());
        $subject = new CharsetRule('dummy/path/file.txt', $this->loadText('utf-8'), 'utf-8-bom');
        self::assertFalse($subject->isValid());
    }

    private function loadText(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/Data/CharsetRuleTest/' . $name . '.txt');
    }
}
