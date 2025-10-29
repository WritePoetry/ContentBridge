<?php

namespace WritePoetry\ContentBridge\Tests\Environment;

use WritePoetry\ContentBridge\Environment\EnvironmentInterface;

class TestEnvironment implements EnvironmentInterface {
    private array $values;

    public function __construct(array $values = []) {
        $this->values = $values;
    }

    public function get(string $key): ?string {
        return $this->values[$key] ?? null;
    }
}
