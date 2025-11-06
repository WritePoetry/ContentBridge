<?php


namespace WritePoetry\ContentBridge\Controllers;

use WritePoetry\ContentBridge\Services\ImageProcessor;
use WritePoetry\ContentBridge\Services\WebhookService;

class PostController {
    public function __construct(
        private ImageProcessor $imageProcessor,
        private WebhookService $webhookService
    ) {
    }

    public function registerHooks(): void {
        add_action('updated_post_meta', [$this, 'onThumbnailSet'], 10, 4);
        add_action('save_post', [$this, 'onPostSaved'], 10, 3);
        add_action('transition_post_status', [$this, 'handlePublish'], 10, 3);
        add_action('post_updated', [$this, 'handleUpdate'], 10, 3);
    }

    public function onThumbnailSet( int $meta_id, int $object_id, string $meta_key, mixed $_meta_value ): void {
        if ($meta_key !== '_thumbnail_id') {
            return;
        }

        $imageId = (int) $_meta_value;
        $this->imageProcessor->cropImage($imageId, 600, 900, 'vertical');
    }

    public function onPostSaved( int $post_id, \WP_Post $post, bool $update ): void {
        // Skip if this is a post revision or if post is not published.
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || get_post_status($post_id) != 'publish') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $this->dispatchWebhook( $post, 'update' );
    }


    public function handlePublish( string $new_status, string $old_status, \WP_Post $post ): void {
        if ('publish' !== $new_status || 'publish' === $old_status) {
            return;
        }

        $this->dispatchWebhook( $post, 'publish' );
    }

    public function handleUpdate( int $post_ID, \WP_Post $post_after, \WP_Post $post_before ): void {
        if ( 'publish' !== $post_after->post_status ) {
            return;
        }

        $this->webhookService->send( $post_after, 'update' );
    }

    private function dispatchWebhook( \WP_Post $post, string $event ): void {
        // If you need to extend allowed post types, add them here ('post', 'page', 'product').
        $allowed_types = [ 'post' ];
        if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
            return;
        }


        if (! $post instanceof \WP_Post) {
            return;
        }

        $this->webhookService->send( $post, $event );
    }
}
