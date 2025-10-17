<?php

namespace WritePoetry\ContentBridge\Services;

use WritePoetry\ContentBridge\Interfaces\ServiceInterface;

class GoogleSheetsService implements ServiceInterface
{
    public function __construct( private HttpClientService $httpClient, private string $url, private string $token ) {}

    public function register_hooks(): void
    {
        // Hook into WordPress save_post action to trigger when any post or page is saved.
        add_action( 'save_post', array( $this, 'handle_publish' ), 10, 3 );
    }


    public function handle_publish( int $post_id ): void {
            // Skip if this is a post revision or if post is not published.
            if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || get_post_status( $post_id ) != 'publish' ) {
                return;
            }

            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }


            if ( 'post' !== get_post_type( $post_id ) && 'page' !== get_post_type( $post_id ) ) {
                return;
            }

            $this->send_to_webhook( $post );
    }


    public function send_to_webhook( int $post_id ): void {

        $this->httpClient->post( $this->webhookUrl, $data, [ 'Authorization' => 'Bearer ' . $token ], 30 );
 

        // Registra l'errore nei log di WordPress.
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            
            error_log(  'Google Sheets Sync Error: ' . $error_message );
        }

        // Check HTTP response code.
        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            error_log( 'Google Sheets Sync HTTP Error: ' . $response_code );
            return;
        }

    

    }
}

