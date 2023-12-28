<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandValidCaseTest extends AbstractTestCase
{
    protected $editorConfig = <<<TXT
root = true

[*]
insert_final_newline = true
TXT;

    protected $files = [
        'valid.txt' => <<<TXT
This is valid text

TXT,
    ];


    public function setUp(): void
    {
        parent::setUp();
        copy('docs/images/editorconfig-logo.png', $this->workspacePath . '/image.png');
    }


    public function testValidCase()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--no-progress' => true, '-s' => ['charset']]);

        self::assertSame(0, $commandTester->getStatusCode(), $commandTester->getDisplay());
        self::assertStringContainsString('Done. No issues found.', $commandTester->getDisplay());
        self::assertStringContainsString('Duration: ', $commandTester->getDisplay());
    }
}
