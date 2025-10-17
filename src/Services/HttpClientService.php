<?php

namespace WritePoetry\ContentBridge\Services;

use WritePoetry\ContentBridge\Interfaces\HttpClientInterface;

class HttpClientService
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {}
    
    public function post(
        string $url,
        array $payload, 
        array $headers = array(),
        int $timeout = 30
    ): void {
        $this->httpClient->post( $url, $payload, $headers, $timeout );
    }
}
