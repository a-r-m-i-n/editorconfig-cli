<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Utility;

class StringFormatUtility
{
    public static function buildScanResultMessage(int $amountIssues, int $amountFilesWithIssues, string $text = 'Found'): string
    {
        if ($amountIssues < 1) {
            return 'No issues ' . strtolower($text) . '.';
        }

        $issueText = 1 === $amountIssues ? 'issue' : 'issues';
        $filesText = 1 === $amountFilesWithIssues ? 'file' : 'files';

        return sprintf('%s %d %s in %d %s', $text, $amountIssues, $issueText, $amountFilesWithIssues, $filesText);
    }
}
