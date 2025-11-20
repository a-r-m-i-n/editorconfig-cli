<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class CommandNoErrorOnExitTest extends AbstractTestCase
{
    protected string $editorConfig = <<<TXT
        root = true

        [*]
        insert_final_newline = true
        TXT;

    protected array $files = [
        'invalid.txt' => <<<TXT
            Missing new line in this file.
            TXT,
    ];

    public function testNoErrorOnExit(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--no-error-on-exit' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('Bypassing error code 2', $commandTester->getDisplay());
        $this->assertStringContainsString('Found 1 issue in 1 file', $commandTester->getDisplay());
    }
}
