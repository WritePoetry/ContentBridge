<?php
/**
 * Dependency Injection container configuration for ContentBridge.
 *
 * Defines service bindings, adapters, and factories for the plugin.
 *
 * @package ContentBridge
 */

use DI\ContainerBuilder;
use WritePoetry\ContentBridge\Controllers\PostController;
use WritePoetry\ContentBridge\Adapters\{
	PhpLoggerAdapter,
	WordPressImageAdapter,
	WordPressHttpClientAdapter,
};
use WritePoetry\ContentBridge\Interfaces\{
	LoggerInterface,
	HttpClientInterface,
	ImageAdapterInterface
};
use WritePoetry\ContentBridge\Services\{
	GoogleSheetsService,
	HttpClientService,
	ImageProcessor,
	JwtGenerator,
	WebhookService,
};
use WritePoetry\ContentBridge\Factories\WebhookPayloadFactory;


$jwt_secret = defined('N8N_JWT_SECRET') 
    ? N8N_JWT_SECRET 
    : getenv('N8N_JWT_SECRET');

if ( ! $jwt_secret ) {
	throw new \RuntimeException( 'N8N_JWT_SECRET must be defined in wp-config.php or plugin config.' );
}

$webhook_url = defined('N8N_WEBHOOK_URL') 
    ? N8N_WEBHOOK_URL 
    : getenv('N8N_WEBHOOK_URL');

if ( ! $webhook_url ) {
    throw new \RuntimeException('N8N_WEBHOOK_URL must be defined in wp-config.php, plugin config, or via environment.');
}


$webapp_url = defined('WEB_APP_URL') 
    ? WEB_APP_URL 
    : getenv('WEB_APP_URL');

if ( ! $webapp_url ) {
    throw new \RuntimeException('WEBAPP_URL must be defined in wp-config.php, plugin config, or via environment.');
}

$webapp_token = defined('WEB_APP_TOKEN') 
    ? WEB_APP_TOKEN 
    : getenv('WEB_APP_TOKEN');

if ( ! $webapp_token ) {
    throw new \RuntimeException('WEB_APP_TOKEN must be defined in wp-config.php, plugin config, or via environment.');
}

$builder = new ContainerBuilder();


$builder->addDefinitions(
	array(
		WebhookService::class        => DI\create( WebhookService::class )
			->constructor(
				DI\get( JwtGenerator::class ),
				$webhook_url,
				$jwt_secret,
				DI\get( HttpClientService::class ),
				DI\get( LoggerInterface::class ),
				DI\get( WebhookPayloadFactory::class )
			),
		JwtGenerator::class          => DI\create( JwtGenerator::class )
				->constructor( $jwt_secret ),
		ImageProcessor::class        => DI\create( ImageProcessor::class ),
		HttpClientService::class     => DI\create( HttpClientService::class )
				->constructor(
					DI\get( HttpClientInterface::class )
				),
		GoogleSheetsService::class   => DI\create( GoogleSheetsService::class )
				->constructor(
					DI\get( HttpClientService::class ),
					$webapp_url, // URL of your deployed Google Apps Script WebApp.
					$webapp_token // Security token (optional but recommended).
				),
		PostController::class        => DI\create( PostController::class )
				->constructor(
					DI\get( ImageProcessor::class ),
					DI\get( WebhookService::class )
				),
		WebhookPayloadFactory::class => DI\create( WebhookPayloadFactory::class )
				->constructor(
					DI\get( ImageAdapterInterface::class ),
					DI\get( ImageProcessor::class )
				),
		HttpClientInterface::class   => DI\autowire( WordPressHttpClientAdapter::class ),
		LoggerInterface::class       => DI\autowire( PhpLoggerAdapter::class ),
		ImageAdapterInterface::class => DI\autowire( WordPressImageAdapter::class ),

	)
);



$container = $builder->build();

return $container;
