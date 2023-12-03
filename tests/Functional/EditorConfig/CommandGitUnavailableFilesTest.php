<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandGitUnavailableFilesTest extends AbstractTestCase
{
    private const GIT_BINARY = 'git';

    protected $editorConfig = <<<TXT
root = true

[*]
insert_final_newline = true

TXT;

    public function setUp(): void
    {
        $this->workspacePath = sys_get_temp_dir() . '/current_editorconfig_cli_test';

        parent::setUp();

        // Copy test files
        copy(__DIR__ . '/../../Fixtures/image.jpg', $this->workspacePath . '/' . 'image.jpg');
        copy(__DIR__ . '/../../Fixtures/document.pdf', $this->workspacePath . '/' . 'document.pdf');
        copy(__DIR__ . '/../../Fixtures/kreis-weiß.svg', $this->workspacePath . '/' . 'kreis-weiß.svg');

        // Set up Git repository for testing
        exec('cd ' . $this->workspacePath . ' && ' . self::GIT_BINARY . ' init', $result, $returnCode);
        if ($returnCode !== 0) {
            throw new \RuntimeException('Unable to create test git repository!');
        }

        // Add files to git stage
        exec('cd ' . $this->workspacePath . ' && ' . self::GIT_BINARY . ' add image.jpg');
        exec('cd ' . $this->workspacePath . ' && ' . self::GIT_BINARY . ' add document.pdf');
        exec('cd ' . $this->workspacePath . ' && ' . self::GIT_BINARY . ' add kreis-weiß.svg');

        // Remove files physically
        exec('rm -f ' . $this->workspacePath . '/image.jpg');
        exec('rm -f ' . $this->workspacePath . '/document.pdf');
    }

    public function testUnavailableFiles()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--no-progress' => true, '--git-only' => true]);

        self::assertSame(1, $commandTester->getStatusCode());
        self::assertStringContainsString('[WARNING] Found 2 unavailable files not being scanned!', $commandTester->getDisplay());
        self::assertStringContainsString('* ' . $this->workspacePath . '/document.pdf', $commandTester->getDisplay());
        self::assertStringContainsString('* ' . $this->workspacePath . '/image.jpg', $commandTester->getDisplay());
    }
}
