<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Unit\EditorConfig\Rules\Line;

use Armin\EditorconfigCli\EditorConfig\Rules\Line\IndentionRule;
use Armin\EditorconfigCli\EditorConfig\Rules\UnfixableException;
use PHPUnit\Framework\TestCase;

class IndentionRuleTest extends TestCase
{
    public function testDetectWrongIndentationsCorrectly(): void
    {
        foreach ([null, 0, 1, 4] as $indentSize) {
            $subject = new IndentionRule('dummy/path/file.txt', '    Non Trailing', 'space', $indentSize);
            $this->assertTrue($subject->isValid());
            $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n    Non Trailing\n    Non Trailing\n", 'space', $indentSize);
            $this->assertTrue($subject->isValid());
            $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n\tNon Trailing\n    Non Trailing\n", 'space', $indentSize);
            $this->assertFalse($subject->isValid());
            $subject = new IndentionRule('dummy/path/file.txt', "\tNon Trailing\n\tNon Trailing\n\tNon Trailing\n", 'tab', $indentSize);
            $this->assertTrue($subject->isValid());
            $subject = new IndentionRule('dummy/path/file.txt', "\tNon Trailing\n\tNon Trailing\n    Non Trailing\n", 'tab', $indentSize);
            $this->assertFalse($subject->isValid());

            $mixedTabsAndSpaces = ":root {\n\t--primary: #720058;\n\n    --secondary: #f00;\n\n\t--variable: #00f;\n\n \t--tertiary: #0f0;\n\n\n  }";
            $subject = new IndentionRule('dummy/path/file.txt', $mixedTabsAndSpaces, 'tab', $indentSize);
            $this->assertFalse($subject->isValid());
            $this->assertCount(3, $subject->getErrors());
            $this->assertSame(4, $subject->getErrors()[0]->getLine());
            $this->assertSame('Expected indention style "tab" but found "spaces"', $subject->getErrors()[0]->getMessage());
            $this->assertSame(8, $subject->getErrors()[1]->getLine());
            $this->assertSame('Expected indention style "tab" but found "spaces"', $subject->getErrors()[1]->getMessage());
            $this->assertSame(11, $subject->getErrors()[2]->getLine());
            $this->assertSame('Expected indention style "tab" but found "spaces"', $subject->getErrors()[2]->getMessage());
        }
    }

    public function testDetectWrongStrictModeIndentationsCorrectly(): void
    {
        $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n    Non Trailing\n    Non Trailing\n", 'space', 4, true);
        $this->assertTrue($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "   Non Trailing\n     Non Trailing\n    Non Trailing\n", 'space', 4, true);
        $this->assertFalse($subject->isValid());
        $this->assertCount(2, $subject->getErrors());
        $this->assertSame('Expected 0 or 4 spaces, found 3', $subject->getErrors()[0]->getMessage());
        $this->assertSame('Expected 4 or 8 spaces, found 5', $subject->getErrors()[1]->getMessage());

        $subject = new IndentionRule('dummy/path/file.txt', "Non Trailing\n Non Trailing\n  Non Trailing\n   Non Trailing\n    Non Trailing\n", 'space', 2, true);
        $this->assertFalse($subject->isValid());
        $this->assertCount(2, $subject->getErrors());
        $this->assertSame('Expected 0 or 2 spaces, found 1', $subject->getErrors()[0]->getMessage());
        $this->assertSame('Expected 2 or 4 spaces, found 3', $subject->getErrors()[1]->getMessage());
    }

    public function testDetectWrongIndentationsCorrectlyWhenUppercase(): void
    {
        $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n    Non Trailing\n    Non Trailing\n", 'SPACE', 4);
        $this->assertTrue($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "    Non Trailing\n\tNon Trailing\n    Non Trailing\n", 'SPACE', 4);
        $this->assertFalse($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "\tNon Trailing\n\tNon Trailing\n\tNon Trailing\n", 'TAB', 4);
        $this->assertTrue($subject->isValid());
        $subject = new IndentionRule('dummy/path/file.txt', "\tNon Trailing\n\tNon Trailing\n    Non Trailing\n", 'TAB', 4);
        $this->assertFalse($subject->isValid());
    }

    public function testDetectMixedIndentationsCorrectly(): void
    {
        $subject = new IndentionRule('dummy/path/file.json', "{\n    \"indent-spaces\": true,\n\t\"indent-tabs\": true}", 'space', 4);
        $this->assertFalse($subject->isValid());
        $this->assertSame('Line 3: Expected indention style "space" but found "tabs"', $subject->getErrorsAsText());

        $subject = new IndentionRule('dummy/path/file.json', "{\n    \"indent-spaces\": true,\n\t\"indent-tabs\": true}", 'tab', 4);
        $this->assertFalse($subject->isValid());
        $this->assertSame('Line 2: Expected indention style "tab" but found "spaces"', $subject->getErrorsAsText());
    }

    public function testFixingIndentionWorks(): void
    {
        $nonStrictText = "    Non Trailing1\n     Non Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $nonStrictText, 'space', 4);
        $result = $subject->fixContent($nonStrictText);
        $this->assertSame($nonStrictText, $result, $result);

        $correText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $wrongText = "    Non Trailing1\n\tNon Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 4);
        $result = $subject->fixContent($wrongText);
        $this->assertSame($correText, $result, $result);

        $correText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $wrongText = "\tNon Trailing1\n\tNon Trailing2\n\tNon Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 4);
        $result = $subject->fixContent($wrongText);
        $this->assertSame($correText, $result, $result);

        $correText = "\tNon Trailing1\n\tNon Trailing2\n\tNon Trailing3\n";
        $wrongText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'tab', 4);
        $result = $subject->fixContent($wrongText);
        $this->assertSame($correText, $result, $result);

        $correText = "\tNon Trailing1\n\tNon Trailing2\n\tNon Trailing3\n";
        $wrongText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'tab', 4);
        $result = $subject->fixContent($wrongText);
        $this->assertSame($correText, $result, $result);

        $wrongText = ":root {\n\t--primary: #720058;\n\n    --secondary: #f00;\n\n\t--variable: #00f;\n\n \t--tertiary: #0f0;\n\t --black: #000;\n\n\n  }";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'tab', 4);
        $correText = ":root {\n\t--primary: #720058;\n\n\t--secondary: #f00;\n\n\t--variable: #00f;\n\n\t--tertiary: #0f0;\n\t --black: #000;\n\n\n}";
        $result = $subject->fixContent($wrongText);
        $this->assertSame($correText, $result, $result);

        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 2);
        $correText = ":root {\n  --primary: #720058;\n\n    --secondary: #f00;\n\n  --variable: #00f;\n\n   --tertiary: #0f0;\n   --black: #000;\n\n\n  }";
        $result = $subject->fixContent($wrongText);
        $this->assertSame($correText, $result, $result);
    }

    public function testThrowUnfixableExceptionIfNoIndentSizeIsGivenForTab(): void
    {
        $fileContent = '    Space indention given';
        $subject = new IndentionRule('dummy/path/file.txt', $fileContent, 'tab', null);
        $this->expectException(UnfixableException::class);
        $this->expectExceptionCode(1763644380);
        $subject->fixContent($fileContent);
    }

    public function testThrowUnfixableExceptionIfNoIndentSizeIsGivenForSpaces(): void
    {
        $fileContent = "\tTab indention given";
        $subject = new IndentionRule('dummy/path/file.txt', $fileContent, 'space', null);
        $this->expectException(UnfixableException::class);
        $this->expectExceptionCode(1763644381);
        $subject->fixContent($fileContent);
    }

    public function testThrowUnfixableExceptionIfNoIndentSizeIsGivenForStrictMode(): void
    {
        $fileContent = '   Unusual space indention given';
        $subject = new IndentionRule('dummy/path/file.txt', $fileContent, 'space', null, true);
        $this->expectException(UnfixableException::class);
        $this->expectExceptionCode(1763644382);
        $subject->fixContent($fileContent);
    }

    public function testFixingIndentionInStrictModeWorks(): void
    {
        $correText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $wrongText = "    Non Trailing1\n     Non Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 4, true);
        $result = $subject->fixContent($wrongText);
        $this->assertSame($correText, $result, $result);

        $correText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $wrongText = "    Non Trailing1\n\tNon Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 4, true);
        $result = $subject->fixContent($wrongText);
        $this->assertSame($correText, $result, $result);

        $correText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $wrongText = "\tNon Trailing1\n\tNon Trailing2\n\tNon Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'space', 4, true);
        $result = $subject->fixContent($wrongText);
        $this->assertSame($correText, $result, $result);

        $correText = "\tNon Trailing1\n\tNon Trailing2\n\tNon Trailing3\n";
        $wrongText = "    Non Trailing1\n    Non Trailing2\n    Non Trailing3\n";
        $subject = new IndentionRule('dummy/path/file.txt', $wrongText, 'tab', 4, true);
        $result = $subject->fixContent($wrongText);
        $this->assertSame($correText, $result, $result);
    }
}
