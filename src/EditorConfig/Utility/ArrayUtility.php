<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Utility;

class ArrayUtility
{
    /**
     * This method allows you to pass a combination of array items and strings with separated values.
     * e.g. ['abc', null, 'def,efg'] will return ['abc', 'def', 'efg'].
     *
     * @param array<int, string|null>|null $arguments Strings in array may contain separated values
     * @param non-empty-string             $separator
     *
     * @return string[]
     */
    public static function flattenSeparatedValues(?array $arguments, string $separator = ','): array
    {
        if (!$arguments) {
            return [];
        }

        $flattenArguments = [];
        foreach ($arguments as $argument) {
            foreach (explode($separator, $argument ?? '') as $flatArgument) {
                $flatArgument = trim($flatArgument);
                if (!empty($flatArgument)) {
                    $flattenArguments[] = $flatArgument;
                }
            }
        }

        return $flattenArguments;
    }
}
