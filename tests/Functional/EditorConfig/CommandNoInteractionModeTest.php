<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandNoInteractionModeTest extends AbstractTestCase
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

    public function setUp(): void
    {
        parent::setUp();
        // Create 500 valid text files
        for ($i = 1; $i <= 500; ++$i) {
            file_put_contents($this->workspacePath . '/valid' . $i . '.txt', 'Test file no. ' . $i . PHP_EOL);
        }
    }

    public function testAskForConfirmation(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['n']);
        $commandTester->execute(['-d' => $this->workspacePath]);
        $this->assertStringContainsString('Found 501 files to scan.', $commandTester->getDisplay());
        $this->assertStringContainsString('Canceled.', $commandTester->getDisplay());
        $this->assertSame(3, $commandTester->getStatusCode());

        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(['-d' => $this->workspacePath]);
        $this->assertSame(2, $commandTester->getStatusCode());
        $this->assertStringContainsString('Found 1 issue in 1 file', $commandTester->getDisplay());
    }

    public function testSkipAskingForConfirmationInNoInteractionMode(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '-n' => true]);
        $this->assertStringContainsString('Found 501 files to scan.', $commandTester->getDisplay());
        $this->assertSame(2, $commandTester->getStatusCode());
        $this->assertStringContainsString('Found 1 issue in 1 file', $commandTester->getDisplay());

        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--no-interaction' => true]);
        $this->assertStringContainsString('Found 501 files to scan.', $commandTester->getDisplay());
        $this->assertSame(2, $commandTester->getStatusCode());
        $this->assertStringContainsString('Found 1 issue in 1 file', $commandTester->getDisplay());
    }
}
