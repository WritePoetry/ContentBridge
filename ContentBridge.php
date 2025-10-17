<?php
/**
 * Plugin Name:     ContentBridge
 * Plugin URI:      https://github.com/WritePoetry/ContentBridge
 * Description:     Sends post data to a specified n8n webhook URL upon publication, with JWT authentication.
 * Author:          Giacomo Secchi
 * Author URI:      https://resume.giacomosecchi.com/
 * Text Domain:     ContentBridge
 * Domain Path:     /languages
 * Version:         0.1.10
 *
 * @package         ContentBridge
 * Update URI:      https://wordpress-1181065-5783234.cloudwaysapps.com
 */

// Your code starts here.
use WritePoetry\ContentBridge\Services\WebhookService;
use WritePoetry\ContentBridge\Services\GoogleSheetsService;

// Load the autoloader.
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

( new \Fragen\Git_Updater\Lite( __FILE__ ) )->run();


$container = require_once __DIR__ . '/bootstrap/container.php';

add_action( 'plugins_loaded', function () use ( $container ) {
    foreach ( [
        WebhookService::class,
        GoogleSheetsService::class,
    ] as $serviceClass ) {
        $service = $container->get( $serviceClass );
        $service->register_hooks();
    }
} );