<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\Tests\Functional\EditorConfig;

use PHPUnit\Framework\TestCase;

/**
 * Creates and cleans up .editorconfig and any amount of files
 * in dedicated workspace path before and after testing.
 */
abstract class AbstractTestCase extends TestCase
{
    protected const LF = "\n";
    protected const CRLF = "\r\n";
    protected const CR = "\r";

    /**
     * @var string On this location (relativ to current working dir) the given files and
     *             .editorconfig are created during set up.
     */
    protected string $workspacePath = '.build/.cache/current_test';

    /**
     * @var string .editorconfig content
     */
    protected string $editorConfig = '';

    /**
     * @var array<string, string> Key is filename, value is file content
     */
    protected array $files = [];

    public function setUp(): void
    {
        if (!is_dir($this->workspacePath)) {
            mkdir($this->workspacePath, 0777, true);
        } else {
            $this->cleanUpWorkspace();
        }

        file_put_contents($this->workspacePath . '/.editorconfig', $this->editorConfig);
        foreach ($this->files as $filePath => $fileContents) {
            file_put_contents($this->workspacePath . '/' . $filePath, $fileContents);
        }

        putenv('COLUMNS=240');
    }

    public function tearDown(): void
    {
        $this->cleanUpWorkspace();
    }

    private function cleanUpWorkspace(): void
    {
        exec('rm -f ' . $this->workspacePath . '/* ' . $this->workspacePath . '/.editorconfig');
    }

    protected function appendContentToEditorConfig(string $contentToAppend): void
    {
        $this->editorConfig .= PHP_EOL . $contentToAppend;
        file_put_contents($this->workspacePath . '/.editorconfig', $this->editorConfig);
    }
}
