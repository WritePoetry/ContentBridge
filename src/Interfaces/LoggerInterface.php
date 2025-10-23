<?php


namespace WritePoetry\ContentBridge\Interfaces;

interface LoggerInterface {
    public function log( string $message ): void;
    public function error( string $message ): void;
    public function info( string $message ): void;
}
