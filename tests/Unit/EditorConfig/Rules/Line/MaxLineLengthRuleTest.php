<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\Line;

use Armin\EditorconfigCli\EditorConfig\Rules\Line\MaxLineLengthRule;
use Armin\EditorconfigCli\EditorConfig\Rules\UnfixableException;
use PHPUnit\Framework\TestCase;

class MaxLineLengthRuleTest extends TestCase
{
    public function testDetectMaxLineLengthCorrectly(): void
    {
        $subject = new MaxLineLengthRule('dummy/path/file.txt', 'This text is 29 chars long.', 20);
        $this->assertFalse($subject->isValid());
        $subject = new MaxLineLengthRule('dummy/path/file.txt', 'This text is 29 chars long.', 29);
        $this->assertTrue($subject->isValid());
        $subject = new MaxLineLengthRule('dummy/path/file.txt', 'This text is 29 chars long.', 30);
        $this->assertTrue($subject->isValid());
    }

    public function testFixingMaxLineLengthNotSupported(): void
    {
        $subject = new MaxLineLengthRule('dummy/path/file.txt', 'ABC', 1);

        $this->expectException(UnfixableException::class);
        $subject->fixContent('ABC');
    }
}
