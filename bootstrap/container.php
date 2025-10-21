<?php

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


if ( ! defined( 'N8N_JWT_SECRET' ) ) {
    throw new \RuntimeException( 'N8N_JWT_SECRET must be defined in wp-config.php or plugin config.' );
}



$builder = new ContainerBuilder();


$builder->addDefinitions( [
    WebhookService::class => DI\create( WebhookService::class )
        ->constructor( 
            DI\get( JwtGenerator::class ),
            N8N_WEBHOOK_URL,
            N8N_JWT_SECRET,
            DI\get( ImageProcessor::class ),
            DI\get( HttpClientService::class ),
            DI\get( LoggerInterface::class ),
            DI\get( ImageAdapterInterface::class )
        ),
    JwtGenerator::class => DI\create( JwtGenerator::class )
        ->constructor( N8N_JWT_SECRET ),
    ImageProcessor::class => DI\create( ImageProcessor::class ),
    HttpClientService::class => DI\create( HttpClientService::class )
        ->constructor( 
            DI\get( HttpClientInterface::class )
        ),
    GoogleSheetsService::class => DI\create( GoogleSheetsService::class )
        ->constructor(
            DI\get( HttpClientService::class ),
            WEB_APP_URL, // URL of your deployed Google Apps Script WebApp
            WEB_APP_TOKEN // Security token (optional but recommended)
        ),
    PostController::class => DI\create( PostController::class )
        ->constructor(
            DI\get( ImageProcessor::class ),
            DI\get( WebhookService::class )
        ),
    HttpClientInterface::class => DI\autowire( WordPressHttpClientAdapter::class ),
    LoggerInterface::class => DI\autowire( PhpLoggerAdapter::class ),
    ImageAdapterInterface::class => DI\autowire( WordPressImageAdapter::class )

] );

$container = $builder->build();

return $container;



