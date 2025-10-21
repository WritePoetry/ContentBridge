<?php

namespace WritePoetry\ContentBridge\Services;

use WritePoetry\ContentBridge\Services\ImageProcessor;
use WritePoetry\ContentBridge\Interfaces\{
    ImageAdapterInterface,
    LoggerInterface,
    ServiceInterface
};

class WebhookService implements ServiceInterface
{
    public function __construct(
        private JwtGenerator $token,
        private string $webhookUrl,
        private string $secret, 
        private ImageProcessor $imageProcessor,
        private HttpClientService $httpClient,
        private LoggerInterface $logger,
        private ImageAdapterInterface $imageAdapter
    ) {}

 

    public function send( \WP_Post $post ): void
    {
        if ( empty( $this->webhookUrl ) || empty( $this->secret )) {
            return;
        }

        $payload = array(
            'post_id' => $post->ID,
            'title'   => $post->post_title
        );


        $sizes = $this->imageAdapter->getFeaturedImageData( $post->ID );
        $featuredImageId = get_post_thumbnail_id( $post->ID );

        // Aggiungi il crop verticale.
        $sizes['vertical'] = $this->imageProcessor->getUrlFromId( $featuredImageId, 'vertical' );

        
        $data = [
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
            'site_name' => get_bloginfo( 'name' ),
            'site_description' => get_bloginfo( 'description' ),
            'site_url' => get_bloginfo( 'url' ),
            'admin_email' => get_bloginfo( 'admin_email' ),
            'author_name' => get_the_author_meta( 'display_name', $post->post_author ),
            'author_email' => get_the_author_meta( 'user_email', $post->post_author ),
            'brevo' => [
                'list_ids' => BREVO_LIST_IDS ?? [],
                'sender_id' => BREVO_SENDER_ID,
                'template_id' => BREVO_TEMPLATE_ID ?? null
            ],       
        ];
 
        
      
        $token = $this->token->generate( $payload );

        $this->httpClient->post( $this->webhookUrl, $data, [ 'Authorization' => 'Bearer ' . $token ], 10 );

        
    }
}