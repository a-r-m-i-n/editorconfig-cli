<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\File;

use Armin\EditorconfigCli\EditorConfig\Rules\File\InsertFinalNewLineRule;
use PHPUnit\Framework\TestCase;

class InsertFinalNewLineRuleTest extends TestCase
{
    public function testDetectWrongMissingFinalLineEndingCorrectly(): void
    {
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', "All okay\n");
        $this->assertTrue($subject->isValid());

        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', 'Missing');
        $this->assertFalse($subject->isValid());
    }

    public function testDetectWrongMissingFinalLineEndingCorrectlyWithoutNewlineFormatGiven(): void
    {
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', "All okay\n", null);
        $this->assertTrue($subject->isValid());

        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', 'Missing', null);
        $this->assertFalse($subject->isValid());
    }

    public function testFixMissingFinalLineEndingWorks(): void
    {
        $wrongText = "\n\nMissing";
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', $wrongText, "\n");
        $result = $subject->fixContent($wrongText);
        $this->assertStringEndsWith("\n", $result);
    }

    public function testFixMissingFinalLineEndingWorksWithoutNewlineFormatGiven(): void
    {
        $wrongText = "\n\nMissing";
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', $wrongText, null);
        $result = $subject->fixContent($wrongText);
        $this->assertStringEndsWith("\n", $result);
    }

    public function testDoNotTouchIfAllOkay(): void
    {
        $correctText = "All okay\n";
        $subject = new InsertFinalNewLineRule('dummy/path/file.txt', $correctText, "\n");
        $result = $subject->fixContent($correctText);
        $this->assertSame($correctText, $result);
    }
}
