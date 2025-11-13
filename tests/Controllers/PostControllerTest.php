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
use WritePoetry\ContentBridge\Services\ImageProcessor;
use WritePoetry\ContentBridge\Services\WebhookService;
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

        $this->imageProcessor = $this->createMock(ImageProcessor::class);
        $this->webhookService = $this->createMock(WebhookService::class);

        $this->controller = new PostController(
            $this->imageProcessor,
            $this->webhookService
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
            ->times(4)
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
            ->with('transition_post_status', [$this->controller, 'handlePublish'], 10, 3);
        Functions\expect('add_action')
            ->once()
            ->with('save_post', [$this->controller, 'onPostSaved'], 10, 3);

        Functions\expect('add_action')
            ->once()
            ->with('post_updated', [$this->controller, 'handleUpdate'], 10, 3);

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
     * Test that on_post_saved sends a webhook when the post is valid.
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     * @return void
     */
    public function test_on_post_saved_sends_webhook_when_valid(): void
    {

        // Create a mock WP_Post object
        $post = Mockery::mock('overload:WP_Post');
        $post->post_type = 'post';
        $post->post_status = 'publish';
        $post->ID = 1;

        Functions\when('wp_is_post_revision')->justReturn(false);
        Functions\when('wp_is_post_autosave')->justReturn(false);
        Functions\when('get_post_status')->justReturn('publish');
        Functions\when('get_post_type')->justReturn('post');

        $this->webhookService->expects($this->once())->method('send')->with($post);

        $this->controller->onPostSaved(1, $post, false);
    }

    /**
     * Test that handle_publish sends a webhook when a post is published.
     * @return void
     */
    public function test_handle_publish_sends_webhook_when_post_published(): void
    {

        // Create a mock WP_Post object
        $post = Mockery::mock('overload:WP_Post');
        $post->post_type = 'post';
        $post->post_status = 'publish';
        $post->ID = 1;


        $this->webhookService->expects($this->once())
            ->method('send')
            ->with($this->equalTo($post));

        $this->controller->handlePublish('publish', 'draft', $post);
    }
}
