<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandInvalidEndOfLineDeclarationTest extends AbstractTestCase
{
    protected $editorConfig = <<<TXT
root = true

[*]
end_of_line = baum
TXT;

    protected $files = [
        'file.txt' => '    Test',
    ];

    public function testInvalidEndOfLineConfigThrowsException()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath]);

        self::assertSame(1, $commandTester->getStatusCode());
        self::assertStringContainsString('baum is not a valid value for \'end_of_line\'', $commandTester->getDisplay());
    }
}
