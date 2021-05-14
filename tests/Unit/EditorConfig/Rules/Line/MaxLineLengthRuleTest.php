<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\Line;

use Armin\EditorconfigCli\EditorConfig\Rules\Line\MaxLineLengthRule;
use Armin\EditorconfigCli\EditorConfig\Rules\UnfixableException;
use PHPUnit\Framework\TestCase;

class MaxLineLengthRuleTest extends TestCase
{

    public function testDetectMaxLineLengthCorrectly()
    {
        $subject = new MaxLineLengthRule('dummy/path/file.txt', "This text is 29 chars long.", 20);
        self::assertFalse($subject->isValid());
        $subject = new MaxLineLengthRule('dummy/path/file.txt', "This text is 29 chars long.", 29);
        self::assertTrue($subject->isValid());
        $subject = new MaxLineLengthRule('dummy/path/file.txt', "This text is 29 chars long.", 30);
        self::assertTrue($subject->isValid());
    }


    public function testFixingMaxLineLengthNotSupported()
    {
        $subject = new MaxLineLengthRule('dummy/path/file.txt', 'ABC', 1);

        self::expectException(UnfixableException::class);
        $subject->fixContent('ABC');
    }

}
