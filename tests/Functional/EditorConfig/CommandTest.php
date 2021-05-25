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
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--help' => '']);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('CLI tool to validate and auto-fix text files, based on given .editorconfig declarations.', $commandTester->getDisplay());
        self::assertStringContainsString('[options] [--] [<names>...]', $commandTester->getDisplay());
        self::assertStringContainsString('-n, --no-interaction', $commandTester->getDisplay());
    }

    public function testValidCase()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => 'tests/Functional/EditorConfig/Data/valid/', '-v' => '', '-s' => ['charset']]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Done. No issues found.', $commandTester->getDisplay());
    }

    public function testInvalidCase()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => 'tests/Functional/EditorConfig/Data/invalid/']);

        self::assertSame(2, $commandTester->getStatusCode());
        self::assertStringContainsString('/invalid.txt [5]', $commandTester->getDisplay());
        self::assertStringContainsString('This file has line ending "lf" given, but "crlf" is expected', $commandTester->getDisplay());
        self::assertStringContainsString('This file has invalid encoding given! Expected: "latin1", Given: "utf-8"', $commandTester->getDisplay());
        self::assertStringContainsString('This file has trailing whitespaces', $commandTester->getDisplay());
        self::assertStringContainsString('Line 1: Expected indention style "space" but found "tabs"', $commandTester->getDisplay());
        self::assertStringContainsString('Line 5: Max line length (80 chars) exceeded by 123 chars', $commandTester->getDisplay());
        self::assertStringContainsString('This file has no final new line given', $commandTester->getDisplay());
    }

    public function testTrailingWhitespacesVsAddNewLine()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => 'tests/Functional/EditorConfig/Data/trailing-whitespace-vs-add-new-line/']);

        self::assertSame(0, $commandTester->getStatusCode(), $commandTester->getDisplay());
    }

    public function testMissingFinalLineTriggersTrailingWhitespace()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => 'tests/Functional/EditorConfig/Data/missing-final-line-triggers-trailing-whitespace/']);

        self::assertSame(2, $commandTester->getStatusCode());
        self::assertStringContainsString('Found 1 issue in 1 file', $commandTester->getDisplay());
    }

    public function testSkippingRules()
    {
        $command = new Application();
        $command->setAutoExit(false);

        // Test without flag
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => 'tests/Functional/EditorConfig/Data/skip-rules/']);
        self::assertSame(2, $commandTester->getStatusCode());
        self::assertStringContainsString('This file has trailing whitespaces', $commandTester->getDisplay());

        // Test with flag
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => 'tests/Functional/EditorConfig/Data/skip-rules/', '-s' => ['trim']]);

        self::assertStringContainsString('Skipping rules: trim_trailing_whitespace', $commandTester->getDisplay());
        self::assertSame(0, $commandTester->getStatusCode());
    }
}
