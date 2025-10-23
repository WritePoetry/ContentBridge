<?php

namespace WritePoetry\ContentBridge\Factories;

use WritePoetry\ContentBridge\Interfaces\ImageAdapterInterface;
use WritePoetry\ContentBridge\Services\ImageProcessor;


class WebhookPayloadFactory
{
    public function __construct(
        private ImageAdapterInterface $imageAdapter,
        private ImageProcessor $imageProcessor
    ) {}

    public function make(\WP_Post $post): array
    {
        $sizes = $this->imageAdapter->getFeaturedImageData( $post->ID );
        // Fallback to default featured image ID if not set.
        $featuredImageId = get_post_thumbnail_id( $post->ID ) ?: apply_filters( 'writepoetry_contentbridge_default_featured_image', null );

        // Add vertical crop.
        $sizes['vertical'] = $this->imageProcessor->getUrlFromId( $featuredImageId, 'vertical' );

        return [
            'post' => [
                'post_title' => $post->post_title,
                'post_content' => trim( htmlspecialchars( apply_filters( 'the_content', $post->post_content ) ) ),
                'post_excerpt' => $post->post_excerpt ?: wp_trim_words( $post->post_content, 30, '…' ),
                'post_url' => get_permalink( $post->ID ),
                'post_date' => $post->post_date,
                'post_id' => $post->ID,
                'meta_desc'    => get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ),
                'featured_image' => $sizes,
            ],
            'site' => [
                'name' => get_bloginfo( 'name' ),
                'description' => get_bloginfo( 'description' ),
                'url' => get_bloginfo( 'url' ),
                'admin_email' => get_bloginfo( 'admin_email' ),
            ],
            'author' => [
                'name' => get_the_author_meta( 'display_name', $post->post_author ),
                'email' => get_the_author_meta( 'user_email', $post->post_author ),
            ],
            'brevo' => [
                'list_ids' => BREVO_LIST_IDS ?? [],
                'sender_id' => BREVO_SENDER_ID,
                'template_id' => BREVO_TEMPLATE_ID ?? null
            ]
        ];
    }
}





      

        

 