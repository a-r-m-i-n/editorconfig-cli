<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandEmptyFilesTest extends AbstractTestCase
{
    protected string $editorConfig = <<<TXT
        root = true

        [*]
        end_of_line = lf
        insert_final_newline = true
        trim_trailing_whitespace = true
        TXT;

    protected array $files = [
        'empty.txt' => '',
    ];

    public function testEmptyFilesDoNotCauseIssues(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('Done. No issues found.', $commandTester->getDisplay());
    }

    public function testEmptyFilesDoNotGetFixed(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--fix' => true]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('Done. No issues fixed.', $commandTester->getDisplay());
    }
}
