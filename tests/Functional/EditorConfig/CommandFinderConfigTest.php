<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandFinderConfigTest extends AbstractTestCase
{
    protected string $editorConfig = <<<TXT
        root = true

        [*]
        insert_final_newline = true
        TXT;

    protected array $files = [
        'finder-config-invalid.php' => <<<PHP
            <?php

            return 'string';

            PHP,
        'finder-config-invalid2.php' => <<<PHP
            <?php

            return new stdClass();

            PHP,

        'finder-config.php' => <<<PHP
            <?php

            use Symfony\Component\Finder\Finder;

            \$finder = new Finder();
            \$finder
                ->in(\$GLOBALS['finderOptions']['path']);

            return \$finder;

            PHP,

        'valid.txt' => <<<TXT
            This is valid text

            TXT,
        'invalid.txt' => <<<TXT
            This is valid text
            TXT,
    ];

    public function testValidCase(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--finder-config' => 'finder-config.php', '--no-progress' => true]);

        $this->assertSame(2, $commandTester->getStatusCode(), $commandTester->getDisplay());
        $this->assertStringContainsString('Searching with custom Finder instance', $commandTester->getDisplay());
        $this->assertStringContainsString('Found 1 issue in 1 file', $commandTester->getDisplay());
    }

    public function testMissingConfigFile(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['-d' => $this->workspacePath, '--finder-config' => 'not-existing.php', '--no-progress' => true]);

        $this->assertSame(1621342890, $commandTester->getStatusCode(), $commandTester->getDisplay());
        $this->assertStringContainsString('Finder config file', $commandTester->getDisplay());
        $this->assertStringContainsString('not found', $commandTester->getDisplay());
    }

    public function testInvalidConfigFile(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['-d' => $this->workspacePath, '--finder-config' => 'finder-config-invalid.php', '--no-progress' => true]);

        $this->assertSame(1621343069, $commandTester->getStatusCode(), $commandTester->getDisplay());
        $this->assertStringContainsString('Custom Symfony Finder configuration', $commandTester->getDisplay());
        $this->assertStringContainsString('should return an instance of', $commandTester->getDisplay());
        $this->assertStringContainsString('Instead it returns: string', $commandTester->getDisplay());

        $commandTester->execute(['-d' => $this->workspacePath, '--finder-config' => 'finder-config-invalid2.php', '--no-progress' => true]);
        $this->assertStringContainsString('Instead it returns: instance of stdClass', $commandTester->getDisplay());
    }
}
