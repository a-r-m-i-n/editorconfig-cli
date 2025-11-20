<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandValidCaseTest extends AbstractTestCase
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
        copy('docs/images/editorconfig-logo.png', $this->workspacePath . '/image.png');
    }

    public function testValidCase(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--no-progress' => true, '-s' => ['charset']]);

        $this->assertSame(0, $commandTester->getStatusCode(), $commandTester->getDisplay());
        $this->assertStringContainsString('Done. No issues found.', $commandTester->getDisplay());
        $this->assertStringContainsString('Duration: ', $commandTester->getDisplay());
    }
}
