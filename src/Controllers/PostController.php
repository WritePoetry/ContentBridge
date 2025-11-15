<?php

namespace WritePoetry\ContentBridge\Controllers;

use WritePoetry\ContentBridge\Services\{
    ImageProcessor,
    GoogleSheetsService,
    WebhookService

};

class PostController
{
    public function __construct(
        private ImageProcessor $imageProcessor,
        private WebhookService $webhookService,
        private GoogleSheetsService $googleSheetsService
    ) {
    }

    public function registerHooks(): void
    {
        add_action('updated_post_meta', [$this, 'onThumbnailSet'], 10, 4);
        add_action('transition_post_status', [$this, 'handlePublish'], 10, 3);
        add_action('post_updated', [$this, 'handleUpdate'], 10, 3);
    }

    public function onThumbnailSet(int $meta_id, int $object_id, string $meta_key, mixed $_meta_value): void
    {
        if ($meta_key !== '_thumbnail_id') {
            return;
        }

        $imageId = (int) $_meta_value;
        $this->imageProcessor->cropImage($imageId, 600, 900, 'vertical');
    }


    public function handlePublish(string $new_status, string $old_status, \WP_Post $post): void
    {
        if ('publish' !== $new_status || 'publish' === $old_status) {
            return;
        }

        $this->dispatchWebhook($post, 'publish');
    }


    public function handleUpdate(int $post_ID, \WP_Post $post_after, \WP_Post $post_before): void
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

        $this->dispatchWebhook($post_after, 'update');
    }

    private function dispatchWebhook(\WP_Post $post, string $event): void
    {
        if (! $post instanceof \WP_Post) {
            return;
        }

        if ($event === 'update') {
            $this->googleSheetsService->handlePublish();
        }

        if ($post->post_type === 'post') {
            $this->webhookService->send($post, $event);
        }
    }
}
