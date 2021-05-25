<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandSkippingRulesTest extends AbstractTestCase
{
    protected $editorConfig = <<<TXT
root = true

[*]
insert_final_newline = true
trim_trailing_whitespace = true
TXT;

    protected $files = [
        'valid.txt' => <<<TXT
This file has trailing whitespaces.


TXT,
    ];

    public function testSkippingRules()
    {
        $command = new Application();
        $command->setAutoExit(false);

        // Test without flag
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath]);
        self::assertSame(2, $commandTester->getStatusCode());
        self::assertStringContainsString('This file has trailing whitespaces', $commandTester->getDisplay());

        // Test with flag
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '-s' => ['trim']]);

        self::assertStringContainsString('Skipping rules: trim_trailing_whitespace', $commandTester->getDisplay());
        self::assertSame(0, $commandTester->getStatusCode());
    }

    public function testSkippingNotExistingRule()
    {
        $command = new Application();
        $command->setAutoExit(false);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '-s' => ['not-existing-rule']]);

        self::assertSame(1621795334, $commandTester->getStatusCode(), $commandTester->getDisplay());
        self::assertStringContainsString('You try to skip rules which are not existing (not-existing-rule)', $commandTester->getDisplay());
    }
}
