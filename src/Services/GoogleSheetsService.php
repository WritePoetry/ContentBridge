<?php

namespace WritePoetry\ContentBridge\Services;

use WritePoetry\ContentBridge\Interfaces\ServiceInterface;

class GoogleSheetsService implements ServiceInterface
{
    public function __construct( private HttpClientService $httpClient, private string $url, private string $token ) {}

   


    public function handle_publish( int $post_id ): void {
        $this->httpClient->post( WEB_APP_URL, null, [ 'Authorization' => 'Bearer ' . WEB_APP_TOKEN ], 30 );
    }
}

