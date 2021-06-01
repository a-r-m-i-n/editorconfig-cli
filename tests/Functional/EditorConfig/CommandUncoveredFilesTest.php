<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandUncoveredFilesTest extends AbstractTestCase
{
    protected $editorConfig = <<<TXT
root = true

[*.txt]
insert_final_newline = true
TXT;

    protected $files = [
        'valid.txt' => <<<TXT
This is valid text

TXT,
        'uncovered1.md' => 'uncovered content',
        'uncovered2.md' => 'uncovered content',
    ];


    public function testUncoveredFiles()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--uncovered' => true, '--no-progress' => true]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Done. No issues found.', $commandTester->getDisplay());
        self::assertStringContainsString('2 files are not covered by .editorconfig declarations', $commandTester->getDisplay());
        self::assertStringContainsString('uncovered1.md', $commandTester->getDisplay());
        self::assertStringContainsString('uncovered2.md', $commandTester->getDisplay());

        $this->appendContentToEditorConfig(<<<TXT

[*.md]
insert_final_newline = false
charset=utf-8
TXT
);

        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--uncovered' => true, '--no-progress' => true]);
        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('No uncovered files found. Good job!', $commandTester->getDisplay());

    }
}
