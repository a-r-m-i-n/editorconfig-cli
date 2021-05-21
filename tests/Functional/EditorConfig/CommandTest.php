<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

class CommandTest extends TestCase
{

    public function testHelp()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '--help']);
        $process->run();

        $output = $process->getOutput();

        self::assertSame(0, $process->getExitCode());
        self::assertContains('bin/ec [options] [--] [<names>...]', $output);
        self::assertContains('-n, --no-interaction', $output);
    }

    public function testValidCase()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/valid/']);
        $process->run();

        self::assertSame(0, $process->getExitCode());
        self::assertContains('Done. No issues found.', $process->getOutput());
    }

    public function testInvalidCase()
    {
        $process = new Process([PHP_BINARY, 'bin/ec', '-d', 'tests/Functional/EditorConfig/Data/invalid/']);
        $process->run();

        self::assertSame(2, $process->getExitCode());
    }
}
