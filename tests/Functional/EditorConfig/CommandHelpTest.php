<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CommandHelpTest extends TestCase
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
}
