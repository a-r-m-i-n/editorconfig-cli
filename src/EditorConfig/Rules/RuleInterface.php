<?php

declare(strict_types = 1);

namespace FGTCLB\EditorConfig\EditorConfig\Rules;

interface RuleInterface
{
    public function isValid(): bool;

    public function getErrors(): array;

    public function fixContent(string $content): string;
}
