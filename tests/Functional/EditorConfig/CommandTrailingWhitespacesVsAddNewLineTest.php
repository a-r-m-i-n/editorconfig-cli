<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTrailingWhitespacesVsAddNewLineTest extends AbstractTestCase
{
    protected $editorConfig = <<<TXT
root = true

[*]
end_of_line = unset
insert_final_newline = true
trim_trailing_whitespace = true
TXT;

    protected $files = [
        'valid.txt' => <<<TXT
No trailing whitespaces, but the required single new line, at the end of the file.

TXT,
    ];


    public function testTrailingWhitespacesVsAddNewLine()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath]);

        self::assertSame(0, $commandTester->getStatusCode(), $commandTester->getDisplay());
    }
}
