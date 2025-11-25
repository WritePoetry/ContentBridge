<?php

/**
 * Class PostControllerTest
 *
 * @package ContentBridge
 */

namespace WritePoetry\ContentBridge\Tests;

use PHPUnit\Framework\TestCase;
use Mockery;
use Brain\Monkey;
use Brain\Monkey\Functions;
use WritePoetry\ContentBridge\Controllers\PostController;
use WritePoetry\ContentBridge\Services\{
    ImageProcessor,
    WebhookService
};
use WritePoetry\ContentBridge\Tests\Environment\TestEnvironment;

/**
 * Sample test case.
 */
class PostControllerTest extends TestCase
{
    private ImageProcessor $imageProcessor;
    private WebhookService $webhookService;
    private PostController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();


        Functions\when('apply_filters')->alias(function ($hook, $default) {
            if ($hook === 'writepoetry_contentbridge_service_config') {
                return [
                    'service_test' => [
                        'url'       => 'https://example.com',
                        'events'    => ['transition_post_status','post_updated'],
                        'post_type' => ['post'],
                        'payload'   => ['access_key' => 'test'],
                        'timeout'   => 10,
                    ],
                ];
            }
            return $default;
        });

        $this->imageProcessor = $this->createMock(ImageProcessor::class);
        $this->webhookService = $this->createMock(WebhookService::class);

        $this->controller = new PostController(
            $this->imageProcessor,
            $this->webhookService,
        );
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_register_hooks_adds_actions(): void
    {
        Functions\expect('add_action')
            ->times(3)
            ->withAnyArgs();

        $this->controller->registerHooks();
        $this->addToAssertionCount(1);
    }

    public function test_register_hooks_adds_expected_actions(): void
    {
        Functions\expect('add_action')
            ->once()
            ->with('updated_post_meta', [$this->controller, 'onThumbnailSet'], 10, 4);
        Functions\expect('add_action')
            ->once()
            ->with('transition_post_status', [$this->controller, 'transitionPostStatus'], 10, 3);
        Functions\expect('add_action')
            ->once()
            ->with('post_updated', [$this->controller, 'postUpdated'], 10, 3);

        $this->controller->registerHooks();
        $this->addToAssertionCount(1);
    }

    public function test_on_thumbnail_set_calls_cropimage_when_key_matches(): void
    {

        $this->imageProcessor->expects($this->once())
            ->method('cropImage')
            ->with(123, 600, 900, 'vertical');

        $this->controller->onThumbnailSet(1, 2, '_thumbnail_id', 123);
    }

    /**
     * Test that handleUpdate a webhook when the post is valid.
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     * @return void
     */
    public function test_handleUpdate_post_saved_sends_webhook_when_valid(): void
    {
        // Create a mock WP_Post object
        // $post = Mockery::mock('overload:WP_Post');
        $post_before = Mockery::mock('overload:WP_Post');
        $post_before->post_type = 'post';
        $post_before->post_status = 'publish';
        $post_before->post_title = 'Old title';
        $post_before->post_content = 'Old content';
        $post_before->post_modified_gmt = '2025-11-17 12:00:00';
        $post_before->ID = 1;

        $post_after = clone $post_before;
        $post_after->post_title = 'New title'; // cambiamento per trigger
        $post_after->post_modified_gmt = '2025-11-17 12:10:00';

        $this->webhookService->expects($this->once())
            ->method('send')
            ->with($post_after, $this->anything());

        $this->controller->postUpdated(1, $post_after, $post_before);
    }

    /**
     * Test that handle_publish sends a webhook when a post is published.
     * @return void
     */
    public function test_handle_publish_sends_webhook_when_post_published(): void
    {
        // Create a mock WP_Post object
        $post_before = Mockery::mock('overload:WP_Post');
        $post_before->post_type = 'post';
        $post_before->post_status = 'draft';
        $post_before->post_title = 'Title';
        $post_before->post_content = 'Content';
        $post_before->ID = 1;

        $post_after = clone $post_before;
        $post_after->post_status = 'publish';


        $this->webhookService
            ->expects($this->once())
            ->method('send')
            ->with($post_after, $this->anything());

        $this->controller->transitionPostStatus('publish', 'draft', $post_after);
    }
}
