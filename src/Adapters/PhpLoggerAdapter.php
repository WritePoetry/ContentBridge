<?php

namespace WritePoetry\ContentBridge\Adapters;

use WritePoetry\ContentBridge\Interfaces\LoggerInterface;

class PhpLoggerAdapter implements LoggerInterface {
    public function log( string $message ): void {
        error_log($message);
    }

    public function error( string $message ): void {
        error_log('ERROR: ' . $message);
    }

    public function info( string $message ): void {
        error_log('INFO: ' . $message);
    }
}
