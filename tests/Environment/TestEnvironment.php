<?php

namespace WritePoetry\ContentBridge\Tests\Environment;

use WritePoetry\ContentBridge\Environment\EnvironmentInterface;

class TestEnvironment implements EnvironmentInterface
{
    public function __construct(
        private array $values = []
    ) {
        $this->values = $values;
    }

    public function get(string $key): ?string
    {
        return $this->values[$key] ?? null;
    }
}
