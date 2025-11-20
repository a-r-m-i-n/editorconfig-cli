<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CommandHelpTest extends TestCase
{
    public function testHelp(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--help' => '']);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('CLI tool to validate and auto-fix text files, based on given .editorconfig declarations.', $commandTester->getDisplay());
        $this->assertStringContainsString('[options] [--] [<names>...]', $commandTester->getDisplay());
        $this->assertStringContainsString('-n, --no-interaction', $commandTester->getDisplay());
    }
}
