<?php

namespace WritePoetry\ContentBridge\Support;

final class StringHelper
{
    public static function toCamelCase(string $str): string
    {
        $parts = explode('_', $str);
        $camel = array_shift($parts);
        foreach ($parts as $part) {
            $camel .= ucfirst(strtolower($part));
        }
        return $camel;
    }
}
