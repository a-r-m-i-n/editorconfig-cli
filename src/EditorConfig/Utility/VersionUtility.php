<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Utility;

class VersionUtility
{
    public static function getApplicationVersionFromComposerJson(): string
    {
        $data = file_get_contents(__DIR__ . '/../../../composer.json');
        if (!$data) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }
        $json = json_decode($data, true);

        return $json['version'] ?? '';
    }
}
