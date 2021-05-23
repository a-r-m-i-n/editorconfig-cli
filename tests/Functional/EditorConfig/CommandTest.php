<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

class CommandTest extends TestCase
{

    public function testHelp()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '--help']);
        $process->run();

        $output = $process->getOutput();

        self::assertSame(0, $process->getExitCode());
        self::assertContains('bin/ec [options] [--] [<names>...]', $output);
        self::assertContains('-n, --no-interaction', $output);
    }

    public function testValidCase()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/valid/']);
        $process->run();

        self::assertSame(0, $process->getExitCode());
        self::assertContains('Done. No issues found.', $process->getOutput());
    }

    public function testInvalidCase()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/invalid/']);
        $process->run();

        self::assertSame(2, $process->getExitCode());
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
        self::assertContains('Found 1 issue in 1 file', $process->getOutput());
    }

    public function testSkippingRules()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/skip-rules/']);
        $process->run();

        self::assertSame(2, $process->getExitCode());
        self::assertContains('This file has trailing whitespaces.', $process->getOutput());

        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/skip-rules/', '-s', 'trim']);
        $process->run();

        self::assertContains('Skipping rules: trim_trailing_whitespace', $process->getOutput());
        self::assertSame(0, $process->getExitCode());
    }
}
