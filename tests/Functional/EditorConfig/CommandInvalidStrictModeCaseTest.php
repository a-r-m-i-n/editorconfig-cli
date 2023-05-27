<?php declare(strict_types = 1);
namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandInvalidStrictModeCaseTest extends AbstractTestCase
{
    protected $editorConfig = <<<TXT
root = true

[*]
charset = latin1
end_of_line = crlf
insert_final_newline = true
trim_trailing_whitespace = true
max_line_length = 80
indent_style = space
indent_size = 4

[invalid3.txt]
max_line_length = unset
charset = utf-8
end_of_line = lf
indent_style = tab
TXT;

    protected $files = [
        'invalid.txt' => "
	This line has tabs. It should be spaces.
   And this line has spaces, but only 3 instead of 4.
  And this line also has spaces, but only 2 instead of 4.

Also this line, has more chars as the allowed 80 chars. Also this line, has more chars as the allowed 80 chars. (123 chars)

This line has trailing spaces ->   " . "
This line has trailing tabs ->      " . "

",
        'invalid2.txt' => <<<TXT
This file lacks of the required new line at the end.
TXT,
        'invalid3.txt' => "    This line has spaces, but it should be tabs." . self::CRLF .
                          "     This line has one space too much. After fixing, this line should have 2 tab chars." . self::CRLF,
    ];


    public function testInvalidCaseInStrictMode()
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--no-progress' => true, '--strict' => true]);

        self::assertSame(2, $commandTester->getStatusCode(), $commandTester->getDisplay());
        self::assertStringContainsString(DIRECTORY_SEPARATOR . 'invalid.txt [9]', $commandTester->getDisplay());
        self::assertStringContainsString('This file has invalid encoding given! Expected: "latin1", Given: "utf-8"', $commandTester->getDisplay());
        self::assertStringContainsString('This file has line ending "lf" given, but "crlf" is expected', $commandTester->getDisplay());
        self::assertStringContainsString('This file has trailing whitespaces', $commandTester->getDisplay());
        self::assertStringContainsString('Line 2: Expected indention style "space" but found "tabs"', $commandTester->getDisplay());
        self::assertStringContainsString('Line 3: Expected 0 spaces, found 3', $commandTester->getDisplay());
        self::assertStringContainsString('Line 4: Expected 0 spaces, found 2', $commandTester->getDisplay());
        self::assertStringContainsString('Line 6: Max line length (80 chars) exceeded by 123 chars', $commandTester->getDisplay());
        self::assertStringContainsString('Line 8: Trailing whitespaces found', $commandTester->getDisplay());
        self::assertStringContainsString('Line 9: Trailing whitespaces found', $commandTester->getDisplay());
        self::assertStringContainsString('This file has no final new line given', $commandTester->getDisplay());
        self::assertStringContainsString(DIRECTORY_SEPARATOR . 'invalid2.txt [2]', $commandTester->getDisplay());
        self::assertStringContainsString('This file has no final new line given', $commandTester->getDisplay());
        self::assertStringContainsString(DIRECTORY_SEPARATOR . 'invalid3.txt [3]', $commandTester->getDisplay());
        self::assertStringContainsString('Line 1: Expected indention style "tab" but found "spaces"', $commandTester->getDisplay());
        self::assertStringContainsString('Line 2: Expected indention style "tab" but found "spaces"', $commandTester->getDisplay());
        self::assertStringContainsString('Found 14 issues in 3 files', $commandTester->getDisplay());
    }
}
