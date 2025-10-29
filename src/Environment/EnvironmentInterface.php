<?php


namespace WritePoetry\ContentBridge\Environment;

interface EnvironmentInterface {
    public function get(string $key): ?string;
}