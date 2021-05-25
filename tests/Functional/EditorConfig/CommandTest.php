<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class CommandTest extends TestCase
{

    public function testHelp()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '--help']);
        $process->run();

        $output = $process->getOutput();

        self::assertSame(0, $process->getExitCode());
        self::assertStringContainsString('bin/ec [options] [--] [<names>...]', $output);
        self::assertStringContainsString('-n, --no-interaction', $output);
    }

    public function testValidCase()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/valid/']);
        $process->run();

        self::assertSame(0, $process->getExitCode());
        self::assertStringContainsString('Done. No issues found.', $process->getOutput());
    }

    public function testInvalidCase()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/invalid/']);
        $process->run();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('/invalid.txt [5]', $process->getOutput());
        self::assertStringContainsString('This file has line ending "lf" given, but "crlf" is expected', $process->getOutput());
        self::assertStringContainsString('This file has invalid encoding given! Expected: "latin1", Given: "utf-8"', $process->getOutput());
        self::assertStringContainsString('This file has trailing whitespaces', $process->getOutput());
        self::assertStringContainsString('Line 1: Expected indention style "space" but found "tabs"', $process->getOutput());
        self::assertStringContainsString('Line 5: Max line length (80 chars) exceeded by 123 chars', $process->getOutput());
        self::assertStringContainsString('This file has no final new line given', $process->getOutput());
    }

    public function testTrailingWhitespacesVsAddNewLine()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/trailing-whitespace-vs-add-new-line/']);
        $process->run();

        self::assertSame(0, $process->getExitCode(), $process->getOutput());
    }

    public function testMissingFinalLineTriggersTrailingWhitespace()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/missing-final-line-triggers-trailing-whitespace/']);
        $process->run();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('Found 1 issue in 1 file', $process->getOutput());
    }

    public function testSkippingRules()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/skip-rules/']);
        $process->run();

        self::assertSame(2, $process->getExitCode());
        self::assertStringContainsString('This file has trailing whitespaces', $process->getOutput());

        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/skip-rules/', '-s', 'trim']);
        $process->run();

        self::assertStringContainsString('Skipping rules: trim_trailing_whitespace', $process->getOutput());
        self::assertSame(0, $process->getExitCode());
    }
}
