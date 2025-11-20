<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandInvalidIndentStyleDeclarationTest extends AbstractTestCase
{
    protected string $editorConfig = <<<TXT
        root = true

        [*]
        indent_style = baum
        TXT;

    protected array $files = [
        'file.txt' => '    Test',
    ];

    public function testInvalidEndOfLineConfigThrowsException(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath]);

        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertStringContainsString('baum is not a valid value for \'indent_style\'', $commandTester->getDisplay());
    }
}
