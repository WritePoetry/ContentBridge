<?php

namespace WritePoetry\ContentBridge\Services;

use WritePoetry\ContentBridge\Config\PluginConfig;
use WritePoetry\ContentBridge\Interfaces\{
    LoggerInterface,
    ServiceInterface
};
use WritePoetry\ContentBridge\Factories\WebhookPayloadFactory;

class WebhookService implements ServiceInterface {
    public function __construct(
        private PluginConfig $config,
        private JwtGenerator $token,
        private HttpClientService $httpClient,
        private LoggerInterface $logger,
        private WebhookPayloadFactory $payloadFactory
    ) {
    }



    public function send( \WP_Post $post ): void {
        if (empty($this->config->get('n8n_webhook_url')) || empty($this->config->get('n8n_jwt_secret'))) {
            return;
        }

        $payload = $this->payloadFactory->make($post);

        $token = $this->token->generate(
            array(
                'post_id' => $post->ID,
                'title'   => $post->post_title,
            )
        );

        $this->httpClient->post(
            $this->config->get('n8n_webhook_url'),
            $payload,
            array( 'Authorization' => 'Bearer ' . $token ),
            10
        );
    }
}
