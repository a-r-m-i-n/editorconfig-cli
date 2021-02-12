<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Rules;

interface RuleInterface
{
    public function isValid(): bool;

    public function getErrors(): array;

    public function fixContent(string $content): string;
}
