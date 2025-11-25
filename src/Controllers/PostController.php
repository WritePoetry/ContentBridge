<?php

namespace WritePoetry\ContentBridge\Controllers;

use WritePoetry\ContentBridge\Support\StringHelper;
use WritePoetry\ContentBridge\Services\{
    ImageProcessor,
    WebhookService
};

class PostController
{
    /**
     * @var array<string, mixed> $config
     */
    private array $config;

    public function __construct(
        private ImageProcessor $imageProcessor,
        private WebhookService $webhookService,
    ) {
        $this->config = apply_filters('writepoetry_contentbridge_service_config', []);
    }

    /**
     * Register all hooks for this controller.
     */
    public function registerHooks(): void
    {
        add_action('updated_post_meta', [$this, 'onThumbnailSet'], 10, 4);

        foreach ($this->config as $key => $data) {
            foreach ($data['events'] as $hook) {
                $method = StringHelper::toCamelCase($hook);

                // dinamicly add action.
                add_action($hook, [$this, $method], 10, 3);
            }
        }
    }

    public function onThumbnailSet(int $meta_id, int $object_id, string $meta_key, mixed $_meta_value): void
    {
        if ($meta_key !== '_thumbnail_id') {
            return;
        }

        $imageId = (int) $_meta_value;
        $this->imageProcessor->cropImage($imageId, 600, 900, 'vertical');
    }


    public function transitionPostStatus(string $new_status, string $old_status, \WP_Post $post): void
    {
        if ('publish' !== $new_status || 'publish' === $old_status) {
            return;
        }

        $this->dispatch($post);
    }


    public function postUpdated(int $post_ID, \WP_Post $post_after, \WP_Post $post_before): void
    {
        if (
            $post_after->post_title        === $post_before->post_title &&
            $post_after->post_content      === $post_before->post_content &&
            $post_after->post_modified_gmt === $post_before->post_modified_gmt
        ) {
            return;
        }

        // Skip if this is a post revision or if post is not published.
        if ('publish' !== $post_after->post_status) {
            return;
        }

        $this->dispatch($post_after);
    }

    private function dispatch(\WP_Post $post): void
    {
        if (! $post instanceof \WP_Post) {
            return;
        }

        foreach ($this->config as $key => $data) {
            if (!in_array($post->post_type, $data['post_type'], true)) {
                continue;
            }

            $this->webhookService->send($post, $data);
        }
    }
}
