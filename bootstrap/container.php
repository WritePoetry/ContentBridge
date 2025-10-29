<?php
/**
 * Dependency Injection container configuration for ContentBridge.
 *
 * Defines service bindings, adapters, and factories for the plugin.
 *
 * @package ContentBridge
 */

use DI\ContainerBuilder;
use WritePoetry\ContentBridge\Config\PluginConfig;
use WritePoetry\ContentBridge\Environment\{
	EnvironmentInterface, 
	WordPressEnvironment
};

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



 


 
$builder = new ContainerBuilder();


$builder->addDefinitions(
	array(
		PluginConfig::class => DI\create( PluginConfig::class )
			->constructor(
				DI\get( EnvironmentInterface::class )
			),
		WebhookService::class        => DI\create( WebhookService::class )
			->constructor(
				DI\get( PluginConfig::class ),
				DI\get( JwtGenerator::class ),
				DI\get( HttpClientService::class ),
				DI\get( LoggerInterface::class ),
				DI\get( WebhookPayloadFactory::class )
			),
		JwtGenerator::class          => DI\create( JwtGenerator::class )
				->constructor(),
		ImageProcessor::class        => DI\create( ImageProcessor::class ),
		HttpClientService::class     => DI\create( HttpClientService::class )
				->constructor(
					DI\get( HttpClientInterface::class )
				),
		GoogleSheetsService::class   => DI\create( GoogleSheetsService::class )
				->constructor(
					DI\get( PluginConfig::class ),
					DI\get( HttpClientService::class ),
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
		EnvironmentInterface::class => DI\autowire(WordPressEnvironment::class),

	)
);



$container = $builder->build();

return $container;
