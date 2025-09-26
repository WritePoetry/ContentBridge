<?php
/**
 * Plugin Name:     ContentBridge
 * Plugin URI:      https://github.com/WritePoetry/ContentBridge
 * Description:     Sends post data to a specified n8n webhook URL upon publication, with JWT authentication.
 * Author:          Giacomo Secchi
 * Author URI:      https://resume.giacomosecchi.com/
 * Text Domain:     ContentBridge
 * Domain Path:     /languages
 * Version:         0.1.9
 *
 * @package         ContentBridge
 * Update URI:      https://wordpress-1181065-5783234.cloudwaysapps.com
 */

// Your code starts here.

// Load the autoloader.
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

( new \Fragen\Git_Updater\Lite( __FILE__ ) )->run();


add_action('transition_post_status', function ( $new_status, $old_status, $post ) {

	if ( 'publish' !== $new_status || 'publish' === $old_status ) {
        return;
    }

	if ( empty( N8N_WEBHOOK_URL ) || empty( N8N_JWT_SECRET )) {
		return;
	}

	$allowed_types = [ 'post' ]; // If you need to extend allowed post types, add them here ('post', 'page', 'product').
	if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
		return;
	}

    $webhook_url = N8N_WEBHOOK_URL;
	$JWT = generate_jwt_hs256( N8N_JWT_SECRET );	

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
					'vertical' => get_vertical_crop_url( $featured_image_id, 600, 900 ),
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
    
    wp_remote_post( $webhook_url, array(
        'body' => json_encode( $data ),
		'headers' => array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $JWT
		),
	    'timeout' => 10
    ) );

	if ( is_wp_error( $response ) ) {
        error_log( 'Webhook error: ' . $response->get_error_message() );
    } else {
	    error_log( 'Webhook response: ' . print_r( $response, true ) );
    }

}, 10, 3 );

function generate_jwt_hs256( $secret ) {
    // 1. Header
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $header_b64 = rtrim( strtr( base64_encode( $header ), '+/', '-_' ), '=' );

    // 2. Payload
    $payload_data = [
        'iat' => time(),           // Issued at
        'exp' => time() + 3600,    // Expiration (1 ora)
        'sub' => 'wordpress'       // Soggetto (puoi cambiarlo)
    ];

    $payload = json_encode( $payload_data );
    $payload_b64 = rtrim(strtr(base64_encode( $payload ), '+/', '-_' ), '=' );

    // 3. Signature
    $signature = hash_hmac( 'sha256', $header_b64 . '.' . $payload_b64, $secret, true );
    $signature_b64 = rtrim( strtr( base64_encode( $signature ), '+/', '-_' ), '=' ) ;

    // 4. Token
    return $header_b64 . '.' . $payload_b64 . '.' . $signature_b64;
}

/**
 * Genera un ritaglio verticale dell'immagine.
 *
 * @param int $attachment_id ID dell'immagine.
 * @param int $height Altezza desiderata.
 * @param int $width Larghezza desiderata.
 * @return string URL dell'immagine ritagliata.
 */
function get_vertical_crop_url( $attachment_id, $width = 600, $height = 900 ) {
    $image_path = get_attached_file( $attachment_id );
    if ( ! file_exists( $image_path ) ) {
        return '';
    }

    $editor = wp_get_image_editor( $image_path );
    if ( is_wp_error( $editor ) ) {
        return '';
    }

    // Ritaglia l'immagine in verticale.
    $editor->resize( $width, $height, true ); // true = crop forzato
    $dest_file = $editor->generate_filename( 'vertical' );
    $saved = $editor->save( $dest_file );

    if ( is_wp_error( $saved ) ) {
        return '';
    }

    // Ottieni URL.
    $upload_dir = wp_upload_dir();
    $url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $saved['path'] );
    return $url;
}

// Hook into WordPress save_post action to trigger when any post or page is saved.
add_action( 'save_post', function ( $post_id ) {
    // Skip if this is a post revision or if post is not published.
    if ( wp_is_post_revision( $post_id ) || get_post_status( $post_id ) != 'publish' ) {
        return;
    }


	if ( 'post' !== get_post_type( $post_id ) && 'page' !== get_post_type( $post_id ) ) {
		return;
	}

    // URL of your deployed Google Apps Script WebApp
    $web_app_url = WEB_APP_URL;

	// Security token (optional but recommended)
    $token = WEB_APP_TOKEN;

    // Send asynchronous POST request to trigger the Google Sheets update.
    $response = wp_remote_post( $web_app_url, [
        'timeout' => 15,
        'body' => [ 'token' => $token ]
    ] );

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
} );
