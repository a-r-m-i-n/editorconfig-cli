<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandEmptyFilesTest extends AbstractTestCase
{
    protected $editorConfig = <<<TXT
root = true

[*]
end_of_line = lf
insert_final_newline = true
trim_trailing_whitespace = true
TXT;

    protected $files = [
        'empty.txt' => '',
    ];


    public function testEmptyFilesDoNotCauseIssues()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Done. No issues found.', $commandTester->getDisplay());
    }

    public function testEmptyFilesDoNotGetFixed()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--fix' => true]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('Done. No issues fixed.', $commandTester->getDisplay());
    }
}
