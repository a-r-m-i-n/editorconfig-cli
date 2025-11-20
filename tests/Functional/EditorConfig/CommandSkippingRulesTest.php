<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandSkippingRulesTest extends AbstractTestCase
{
    protected string $editorConfig = <<<TXT
        root = true

        [*]
        insert_final_newline = true
        trim_trailing_whitespace = true
        max_line_length = 70
        TXT;

    protected array $files = [
        'invalid.txt' => <<<TXT
            This file has trailing whitespaces.
            But also, this file has a line which is longer, than the rules allow (more than 70 chars)!


            TXT,
        'invalid2.txt' => 'This file is lacking of a final new line.',
    ];

    public function testSkippingRules(): void
    {
        $command = new Application();
        $command->setAutoExit(false);

        // Test without flag
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath]);
        $this->assertSame(2, $commandTester->getStatusCode());
        $this->assertStringContainsString(DIRECTORY_SEPARATOR . 'invalid.txt [2]', $commandTester->getDisplay());
        $this->assertStringContainsString('This file has trailing whitespaces', $commandTester->getDisplay());
        $this->assertStringContainsString('Max line length (70 chars) exceeded by 90 chars', $commandTester->getDisplay());
        $this->assertStringContainsString(DIRECTORY_SEPARATOR . 'invalid2.txt [1]', $commandTester->getDisplay());
        $this->assertStringContainsString('This file has no final new line given', $commandTester->getDisplay());

        // Test with flag
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '-s' => ['trim', 'max_line_length', 'insert_final_newline']]);

        $this->assertStringContainsString('Skipping rules: trim_trailing_whitespace', $commandTester->getDisplay());
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testSkippingNotExistingRule(): void
    {
        $command = new Application();
        $command->setAutoExit(false);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '-s' => ['not-existing-rule']]);

        $this->assertSame(1621795334, $commandTester->getStatusCode(), $commandTester->getDisplay());
        $this->assertStringContainsString('You try to skip rules which are not existing (not-existing-rule)', $commandTester->getDisplay());
    }
}
