<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandWorkingDirTest extends AbstractTestCase
{
    protected $editorConfig = <<<TXT
root = true

[*]
insert_final_newline = true
TXT;

    protected $files = [
        'valid.txt' => <<<TXT
This is valid text

TXT,
    ];

    public function testUsingCwdWhenDirIsNull()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => null, '--no-progress' => true]);

        self::assertSame(0, $commandTester->getStatusCode(), $commandTester->getDisplay());
        self::assertStringContainsString('Searching in directory ' . getcwd(), $commandTester->getDisplay());
        self::assertStringContainsString('Done. No issues found.', $commandTester->getDisplay());
    }

    public function testThrowErrorWhenDirectoryIsNotExisting()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => 'invalid-dir', '--no-progress' => true]);

        self::assertSame(1, $commandTester->getStatusCode(), $commandTester->getDisplay());
        self::assertStringContainsString('[ERROR] Invalid working directory "invalid-dir" given!', $commandTester->getDisplay());
    }
}
