<?php

namespace WritePoetry\ContentBridge\Services;

use WritePoetry\ContentBridge\Interfaces\ServiceInterface;

class WebhookService implements ServiceInterface
{
    public function __construct(
        private JwtGenerator $token,
        private string $webhookUrl,
        private string $secret, 
        private ImageProcessor $imageProcessor,
        private HttpClientService $httpClient
    ) {}

    public function register_hooks(): void
    {
        add_action( 'transition_post_status', array( $this, 'handle_publish' ), 10, 3 );
    }

    public function handle_publish(string $new_status, string $old_status, $post ) : void
    {
        if ( 'publish' !== $new_status || 'publish' === $old_status ) {
            return;
        }

        // If you need to extend allowed post types, add them here ('post', 'page', 'product').
        $allowed_types = array( 'post' );
        if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
            return;
        }

        if ( ! $post instanceof \WP_Post ) {
            return;
        }
        
        $this->send_to_webhook( $post );
    }

    public function send_to_webhook( \WP_Post $post ): void
    {
        if ( empty( $this->webhookUrl ) || empty( $this->secret )) {
            return;
        }

        $payload = array(
            'post_id' => $post->ID,
            'title'   => $post->post_title
        );

        $token = $this->token->generate( $payload );


        
        $featured_image_id = get_post_thumbnail_id( $post->ID );
        $image_metadata = wp_get_attachment_metadata( $featured_image_id );

        $data = array(
            'post' => array(
                'post_title' => $post->post_title,
                'post_content' => trim( htmlspecialchars( apply_filters( 'the_content', $post->post_content ) ) ),
                'post_excerpt' => $post->post_excerpt ?: wp_trim_words( $post->post_content, 30, '…' ),
                'post_url' => get_permalink( $post->ID ),
                'post_date' => $post->post_date,
                'post_id' => $post->ID,
                'meta_desc'    => get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ),
                'featured_image' => array(
                    'url' => get_the_post_thumbnail_url( $post->ID, 'full' ),
                    'alt' => get_post_meta( $featured_image_id, '_wp_attachment_image_alt', true ),
                    'caption' => wp_get_attachment_caption( $featured_image_id ),
                    'width' => $image_metadata['width'] ?? '',
                    'height' => $image_metadata['height'] ?? '',
                    'sizes' => array(
                        'full' => get_the_post_thumbnail_url( $post->ID, 'full' ),
                        'large' => get_the_post_thumbnail_url( $post->ID, 'large' ),
                        'medium' => get_the_post_thumbnail_url( $post->ID, 'medium' ),
                        'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'thumbnail' ),
                        'vertical' => $this->imageProcessor->get_crop_url( $featured_image_id, 600, 900, 'vertical' ),
                    )
                ),
            ), 
            'site_name' => get_bloginfo( 'name' ),
            'site_description' => get_bloginfo( 'description' ),
            'site_url' => get_bloginfo( 'url' ),
            'admin_email' => get_bloginfo( 'admin_email' ),
            'author_name' => get_the_author_meta( 'display_name', $post->post_author ),
            'author_email' => get_the_author_meta( 'user_email', $post->post_author ),
            'brevo' => array(
                'list_ids' => BREVO_LIST_IDS ?? array(),
                'sender_id' => BREVO_SENDER_ID,
                'template_id' => BREVO_TEMPLATE_ID ?? null
            )
        );
        
      

        $this->httpClient->post( $this->webhookUrl, $data, [ 'Authorization' => 'Bearer ' . $token ], 10 );

        if ( is_wp_error( $response ) ) {
            error_log( 'Webhook error: ' . $response->get_error_message() );
        } else {
            error_log( 'Webhook response: ' . print_r( $response, true ) );
        }
    }


}