<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandNoErrorOnExitTest extends AbstractTestCase
{
    protected $editorConfig = <<<TXT
root = true

[*]
insert_final_newline = true
TXT;

    protected $files = [
        'invalid.txt' => <<<TXT
Missing new line in this file.
TXT,
    ];


    public function testNoErrorOnExit()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '-v' => true, '--no-error-on-exit' => true]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Bypassing error code 2', $commandTester->getDisplay());
        self::assertStringContainsString('Found 1 issue in 1 file', $commandTester->getDisplay());
    }
}
