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
        add_action('updated_post_meta', array( $this, 'onThumbnailSet' ), 10, 4);
        add_action('transition_post_status', array( $this, 'handlePublish' ), 10, 3);
        add_action('save_post', array( $this, 'onPostSaved' ), 10, 3);
        // add_action( 'post_updated', [$this, 'on_post_updated'], 10, 3 );
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

        if ('post' !== get_post_type($post_id) && 'page' !== get_post_type($post_id)) {
            return;
        }

        $this->webhookService->send($post);
    }


    public function handlePublish( string $new_status, string $old_status, $post ): void {
        if ('publish' !== $new_status || 'publish' === $old_status) {
            return;
        }

        // If you need to extend allowed post types, add them here ('post', 'page', 'product').
        $allowed_types = array( 'post' );
        if (! in_array($post->post_type, $allowed_types, true)) {
            return;
        }

        if (! $post instanceof \WP_Post) {
            return;
        }

        $this->webhookService->send($post);
    }
}
