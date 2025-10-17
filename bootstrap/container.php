<?php

use DI\ContainerBuilder;
use WritePoetry\ContentBridge\Adapters\WordPressHttpClientAdapter;
use WritePoetry\ContentBridge\Interfaces\HttpClientInterface;
use WritePoetry\ContentBridge\Services\WebhookService;
use WritePoetry\ContentBridge\Services\JwtGenerator;
use WritePoetry\ContentBridge\Services\ImageProcessor;
use WritePoetry\ContentBridge\Services\HttpClientService;
use WritePoetry\ContentBridge\Services\GoogleSheetsService;


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
            DI\get( HttpClientService::class )
        ),
    JwtGenerator::class => DI\create( JwtGenerator::class )
        ->constructor( N8N_JWT_SECRET ),
    ImageProcessor::class => DI\create( ImageProcessor::class ),
    HttpClientService::class => DI\create( HttpClientService::class ),
    GoogleSheetsService::class => DI\create( GoogleSheetsService::class )
        ->constructor(
            DI\get( HttpClientService::class ),
            WEB_APP_URL, // URL of your deployed Google Apps Script WebApp
            WEB_APP_TOKEN // Security token (optional but recommended)
        ),
    //HttpClientInterface::class => DI\autowire(WordPressHttpClientAdapter::class),
] );

$container = $builder->build();

return $container;



