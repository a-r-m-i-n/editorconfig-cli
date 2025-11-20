<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandNothingToDoTest extends AbstractTestCase
{
    protected string $editorConfig = <<<TXT
        root = true

        [*]
        insert_final_newline = true
        TXT;

    protected array $files = [];

    public function testValidCase(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('Nothing to do here.', $commandTester->getDisplay());
    }
}
