<?php

namespace WritePoetry\ContentBridge\Services;

use WritePoetry\ContentBridge\Interfaces\{
    LoggerInterface,
    ServiceInterface
};
use WritePoetry\ContentBridge\Factories\WebhookPayloadFactory;

class WebhookService implements ServiceInterface {
    public function __construct(
        private JwtGenerator $token,
        private string $webhookUrl,
        private string $secret,
        private HttpClientService $httpClient,
        private LoggerInterface $logger,
        private WebhookPayloadFactory $payloadFactory
    ) {
    }



    public function send( \WP_Post $post ): void {
        if (empty($this->webhookUrl) || empty($this->secret)) {
            return;
        }

        $payload = $this->payloadFactory->make($post);

        $token = $this->token->generate(
            array(
                'post_id' => $post->ID,
                'title'   => $post->post_title,
            )
        );

        $this->httpClient->post($this->webhookUrl, $payload, array( 'Authorization' => 'Bearer ' . $token ), 10);
    }
}
