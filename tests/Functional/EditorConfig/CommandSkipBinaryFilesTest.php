<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class CommandSkipBinaryFilesTest extends AbstractTestCase
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

    public function setUp(): void
    {
        parent::setUp();

        // Copy binary test files
        copy(__DIR__ . '/../../Fixtures/image.jpg', $this->workspacePath . '/image.jpg');
        copy(__DIR__ . '/../../Fixtures/document.pdf', $this->workspacePath . '/document.pdf');
    }

    public function testSkipBinaryFiles(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--no-progress' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('Done. No issues found.', $commandTester->getDisplay());
        $this->assertStringContainsString('2 binary files skipped:', $commandTester->getDisplay());
        $this->assertStringContainsString('/document.pdf [application/pdf]', $commandTester->getDisplay());
        $this->assertStringContainsString('/image.jpg [image/jpeg]', $commandTester->getDisplay());
    }
}
