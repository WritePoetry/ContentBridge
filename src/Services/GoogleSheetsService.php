<?php

namespace WritePoetry\ContentBridge\Services;

use WritePoetry\ContentBridge\Config\PluginConfig;
use WritePoetry\ContentBridge\Interfaces\ServiceInterface;

class GoogleSheetsService implements ServiceInterface
{
    public function __construct(
        private PluginConfig $config,
        private HttpClientService $httpClient
    ) {
    }

    public function handlePublish(int $post_id): void
    {
        $this->httpClient->post(
            $this->config->get('webapp_url'),
            null,
            array( 'Authorization' => 'Bearer ' . $this->config->get('webapp_token') ),
            30
        );
    }
}
