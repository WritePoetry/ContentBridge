<?php

namespace WritePoetry\ContentBridge\Adapters;

use WritePoetry\ContentBridge\Interfaces\{
    HttpClientInterface,
    LoggerInterface
};

class WordPressHttpClientAdapter implements HttpClientInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function post(
        string $url,
        ?array $body = null,
        array $headers = array(),
        int $timeout = 30
    ): void {

        $args = array(
            'timeout' => $timeout,
            'headers' => array_merge(
                array( 'Content-Type' => 'application/json' ),
                $headers
            ),
        );

        if (null !== $body) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_post($url, $args);
        $code     = wp_remote_retrieve_response_code($response);

        // Registra l'errore nei log di WordPress.
        if (is_wp_error($response)) {
            $this->logger->error($response->get_error_message());
            throw new \RuntimeException($response->get_error_message());
        }

        // Check HTTP response code.
        if ($code < 200 || $code >= 300) {
            $this->logger->error('HTTP POST Unexpected Response Code: ' . $code);
        }
    }
}
