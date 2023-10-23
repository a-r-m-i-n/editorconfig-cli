<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\File\InsertFinalNewLineRule;
use PHPUnit\Framework\TestCase;

class InsertFinalNewLineRuleTest extends TestCase
{

    public function testDetectWrongMissingFinalLineEndingCorrectly()
    {
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', "All okay\n");
        self::assertTrue($subject->isValid());

        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', "Missing");
        self::assertFalse($subject->isValid());
    }

    public function testDetectWrongMissingFinalLineEndingCorrectlyWithoutNewlineFormatGiven()
    {
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', "All okay\n", null);
        self::assertTrue($subject->isValid());

        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', "Missing", null);
        self::assertFalse($subject->isValid());
    }

    public function testFixMissingFinalLineEndingWorks()
    {
        $wrongText = "\n\nMissing";
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', $wrongText, "\n");
        $result = $subject->fixContent($wrongText);
        self::assertStringEndsWith("\n", $result);
    }

    public function testFixMissingFinalLineEndingWorksWithoutNewlineFormatGiven()
    {
        $wrongText = "\n\nMissing";
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', $wrongText, null);
        $result = $subject->fixContent($wrongText);
        self::assertStringEndsWith("\n", $result);
    }

    public function testDoNotTouchIfAllOkay()
    {
        $correctText = "All okay\n";
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', $correctText, "\n");
        $result = $subject->fixContent($correctText);
        self::assertSame($correctText, $result);
    }

    public function testDoNotTouchIfFileIsEmpty()
    {
        $correctText = "";
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', $correctText, "\n");
        $result = $subject->fixContent($correctText);
        self::assertSame($correctText, $result);
    }
}
