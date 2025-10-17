<?php


namespace WritePoetry\ContentBridge\Interfaces;

interface HttpClientInterface
{
    /**
     * Send a POST request to the specified URL with given payload and headers.
     *
     * @param string $url The endpoint URL.
     * @param array $payload The data to send in the request body.
     * @param array $headers Optional headers to include in the request.
     * @param int $timeout Optional timeout for the request in seconds.
     *
     * @return void
     */
    public function post( string $url, array $payload,  array $headers = array(), int $timeout = 30 ): void;
}