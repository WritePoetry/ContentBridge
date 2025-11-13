<?php

namespace WritePoetry\ContentBridge\Environment;

class WordPressEnvironment implements EnvironmentInterface
{
    public function get(string $key): ?string
    {
        return defined($key) ? constant($key) : getenv($key);
    }
}
