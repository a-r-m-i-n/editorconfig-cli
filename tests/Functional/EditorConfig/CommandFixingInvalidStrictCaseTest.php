<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use Armin\EditorconfigCli\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandFixingInvalidStrictCaseTest extends AbstractTestCase
{
    protected string $editorConfig = <<<TXT
        root = true

        [*]
        charset = latin1
        end_of_line = crlf
        insert_final_newline = true
        trim_trailing_whitespace = true
        max_line_length = 80
        indent_style = space
        indent_size = 4
        TXT;

    protected array $files = [
        'invalid.txt' => self::LF . '	This line has tabs. It should be spaces.
   And this line has spaces, but only 3 instead of 4.
  And this line also has spaces, but only 2 instead of 4.
     And this line has spaces, but 5 instead of 4 or 8.
Also this line, has more chars as the allowed 80 chars. Also this line, has more chars as the allowed 80 chars. (123 chars)

This line has trailing spaces ->   ' . '
This line has trailing tabs ->		' . '

',
    ];

    /**
     * @var array<string, string> Key is filename, value the expected result after fixing
     */
    protected array $expectedResults = [
        'invalid.txt' => self::CRLF . '    This line has tabs. It should be spaces.' . self::CRLF .
'And this line has spaces, but only 3 instead of 4.' . self::CRLF .
'And this line also has spaces, but only 2 instead of 4.' . self::CRLF .
'    And this line has spaces, but 5 instead of 4 or 8.' . self::CRLF .
'Also this line, has more chars as the allowed 80 chars. Also this line, has more chars as the allowed 80 chars. (123 chars)' . self::CRLF . self::CRLF .
'This line has trailing spaces ->' . self::CRLF .
'This line has trailing tabs ->' . self::CRLF,
    ];

    public function testFixingInvalidStrictCase(): void
    {
        $command = new Application();
        $command->setAutoExit(false);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--no-progress' => true, '--strict' => true]);

        $this->assertSame(2, $commandTester->getStatusCode());
        $this->assertStringContainsString(DIRECTORY_SEPARATOR . 'invalid.txt [10]', $commandTester->getDisplay());
        $this->assertStringContainsString('This file has invalid encoding given! Expected: "latin1", Given: "utf-8"', $commandTester->getDisplay());
        $this->assertStringContainsString('This file has line ending "lf" given, but "crlf" is expected', $commandTester->getDisplay());
        $this->assertStringContainsString('This file has trailing whitespaces', $commandTester->getDisplay());
        $this->assertStringContainsString('Line 2: Expected indention style "space" but found "tabs"', $commandTester->getDisplay());
        $this->assertStringContainsString('Line 3: Expected 0 or 4 spaces, found 3', $commandTester->getDisplay());
        $this->assertStringContainsString('Line 4: Expected 0 or 4 spaces, found 2', $commandTester->getDisplay());
        $this->assertStringContainsString('Line 5: Expected 4 or 8 spaces, found 5', $commandTester->getDisplay());
        $this->assertStringContainsString('Line 6: Max line length (80 chars) exceeded by 123 chars', $commandTester->getDisplay());
        $this->assertStringContainsString('Line 8: Trailing whitespaces found', $commandTester->getDisplay());
        $this->assertStringContainsString('Line 9: Trailing whitespaces found', $commandTester->getDisplay());
        $this->assertStringContainsString('Found 10 issues in 1 file', $commandTester->getDisplay());

        $commandTester = new CommandTester($command);
        $commandTester->execute(['-d' => $this->workspacePath, '--no-progress' => true, '--strict' => true, '--fix' => true]);
        foreach ($this->expectedResults as $filename => $expectedResult) {
            $actual = file_get_contents($this->workspacePath . '/' . $filename);
            $this->assertSame($expectedResult, $actual, 'File with unexpected contents: ' . $filename);
        }
    }
}
