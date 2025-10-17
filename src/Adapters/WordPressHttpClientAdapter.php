<?php

class WordPressHttpClientAdapter implements HttpClientInterface
{
    public function post(
        string $url,
        array $payload, 
        array $headers = array(),
        int $timeout = 30
    ): void {
        $response = wp_remote_post( $url, [
            'timeout' => $timeout,
            'headers' => array_merge(
                ['Content-Type' => 'application/json'], 
                $headers    
            ),
            'body' => wp_json_encode( $payload ),
        ] );

        

        if (is_wp_error($response)) {
            error_log('HTTP POST Error: ' . $response->get_error_message());
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        
        if ($code < 200 || $code >= 300) {
            error_log('HTTP POST Unexpected Response Code: ' . $code);
        } 
    }
}
