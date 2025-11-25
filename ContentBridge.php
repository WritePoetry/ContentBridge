<?php

/**
 * Plugin Name:     ContentBridge
 * Plugin URI:      https://github.com/WritePoetry/ContentBridge
 * // phpcs:ignore Generic.Files.LineLength.TooLong
 * Description:     Sends post data to any number of configurable external webhook services with optional JWT authentication.
 * Author:          Giacomo Secchi
 * Author URI:      https://resume.giacomosecchi.com/
 * Text Domain:     contentbridge
 * Domain Path:     /languages
 * Version:         0.2.2
 * License:         GPL v2 or later
 *
 * @package         ContentBridge
 * Update URI:      https://wordpress-1181065-5783234.cloudwaysapps.com
 */

// Your code starts here.
use WritePoetry\ContentBridge\Controllers\PostController;
use WritePoetry\GithubUpdater\UpdaterFactory;

// Load the autoloader.
if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

( new \Fragen\Git_Updater\Lite(__FILE__) )->run();


$container = require_once __DIR__ . '/bootstrap/container.php';

add_action(
    'plugins_loaded',
    function () use ($container) {
        $container->get(PostController::class)->registerHooks();

        // Register the vertical size: 600x900, forced crop.
        add_image_size('vertical', 600, 900, true);
    }
);
