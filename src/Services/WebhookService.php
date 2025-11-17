<?php

namespace WritePoetry\ContentBridge\Services;

use WritePoetry\ContentBridge\Interfaces\{
    LoggerInterface,
    ServiceInterface
};
use WritePoetry\ContentBridge\Factories\WebhookPayloadFactory;

class WebhookService implements ServiceInterface
{
    public function __construct(
        private JwtGenerator $token,
        private HttpClientService $httpClient,
        private LoggerInterface $logger,
        private WebhookPayloadFactory $payloadFactory
    ) {
    }



    public function send(\WP_Post $post, array $spec, ?string $event = null): void
    {

        $url = $spec['url'] ?? null;
        if (!$url) {
            return;
        }

        $payload = $spec['payload'] ?? [];
        if (($spec['payload']['type'] ?? null) === 'post') {
            $payload = $this->payloadFactory->make($post);
            $payload['event'] = $event;
        }

        $headers = [];
        if (!empty($spec['secret'])) {
            $token = $this->token->generate(
                [
                    'post_id' => $post->ID,
                    'title' => $post->post_title
                ],
                $spec['secret']
            );
            $headers['Authorization'] = 'Bearer ' . $token;
        }


        $this->httpClient->post($url, $payload, $headers, $spec['timeout'] ?? 10);
    }
}
