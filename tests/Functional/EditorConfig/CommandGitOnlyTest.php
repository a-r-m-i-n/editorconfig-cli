<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class CommandGitOnlyTest extends TestCase
{

    public function testGitOnlyWorksWithThisRepo()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => getcwd(), '--no-progress' => true, '--git-only' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        self::assertSame(0, $commandTester->getStatusCode(), $commandTester->getDisplay());
        self::assertStringContainsString('Get files from git binary', $commandTester->getDisplay());
    }

    public function testGitOnlyWhenTargetHasNoGitRepo()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => '/', '--no-progress' => true, '--git-only' => true]);

        self::assertSame(128, $commandTester->getStatusCode(), $commandTester->getDisplay());
        self::assertStringContainsString('Git binary returned error code 128 with the following output', $commandTester->getDisplay());
        self::assertStringContainsString('fatal: not a git repository', $commandTester->getDisplay());
    }
}
