<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandWorkingDirTest extends AbstractTestCase
{
    protected string $editorConfig = <<<TXT
        root = true

        [*]
        insert_final_newline = true
        TXT;

    protected array $files = [
        'valid.txt' => <<<TXT
            This is valid text

            TXT,
    ];

    public function testUsingCwdWhenDirIsNull(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => null, '--no-progress' => true]);

        $this->assertSame(0, $commandTester->getStatusCode(), $commandTester->getDisplay());
        $this->assertStringContainsString('Searching in directory ' . getcwd(), $commandTester->getDisplay());
        $this->assertStringContainsString('Done. No issues found.', $commandTester->getDisplay());
    }

    public function testThrowErrorWhenDirectoryIsNotExisting(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => 'invalid-dir', '--no-progress' => true]);

        $this->assertSame(1, $commandTester->getStatusCode(), $commandTester->getDisplay());
        $this->assertStringContainsString('[ERROR] Invalid working directory "invalid-dir" given!', $commandTester->getDisplay());
    }
}
