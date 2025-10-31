<?php
/**
 * Class PostControllerTest
 *
 * @package ContentBridge
 */

namespace WritePoetry\ContentBridge\Tests;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use WritePoetry\ContentBridge\Controllers\PostController;
use WritePoetry\ContentBridge\Services\ImageProcessor;
use WritePoetry\ContentBridge\Services\WebhookService;
use WritePoetry\ContentBridge\Tests\Environment\TestEnvironment;


/**
 * Sample test case.
 */
class PostControllerTest extends TestCase {

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
            ->times(3)
            ->withAnyArgs();

        $this->controller->registerHooks();
        $this->addToAssertionCount(1);
    }

    public function test_register_hooks_adds_expected_actions(): void {
        Functions\expect('add_action')
            ->once()
            ->with('updated_post_meta', [$this->controller, 'onThumbnailSet'], 10, 4);
        Functions\expect('add_action')
            ->once()
            ->with('transition_post_status', [$this->controller, 'handlePublish'], 10, 3);
        Functions\expect('add_action')
            ->once()
            ->with('save_post', [$this->controller, 'onPostSaved'], 10, 3);
        
        $this->controller->registerHooks();
        $this->addToAssertionCount(1);
    }

    public function test_on_thumbnail_set_calls_cropimage_when_key_matches(): void {
        $imageProcessor = $this->createMock(ImageProcessor::class);
        $webhook = $this->createMock(WebhookService::class);
        
        $controller = new PostController($imageProcessor, $webhook);

        $imageProcessor->expects($this->once())
            ->method('cropImage')
            ->with(123, 600, 900, 'vertical');

        $controller->onThumbnailSet(1, 2, '_thumbnail_id', 123);
    }

    public function test_on_post_saved_sends_webhook_when_valid(): void {
        $webhook = $this->createMock(WebhookService::class);
        $imageProcessor = $this->createMock(ImageProcessor::class);
        $controller = new PostController($imageProcessor, $webhook);

       // $post = $this->createStub(\WP_Post::class);

        Functions\when('wp_is_post_revision')->justReturn(false);
        Functions\when('wp_is_post_autosave')->justReturn(false);
        Functions\when('get_post_status')->justReturn('publish');
        Functions\when('get_post_type')->justReturn('post');
        if (defined('DOING_AUTOSAVE')) { runkit_constant_remove('DOING_AUTOSAVE'); }

        // $webhook->expects($this->once())->method('send')->with($post);

        // $controller->onPostSaved(1, $post, false);
    
        $this->markTestSkipped('Temporarily disabled');

    }

    public function test_handle_publish_sends_webhook_when_post_published(): void {
        $webhook = $this->createMock(WebhookService::class);
        $imageProcessor = $this->createMock(ImageProcessor::class);
        $controller = new PostController($imageProcessor, $webhook);

        // $post = new \WP_Post((object)['post_type' => 'post']);
        // $webhook->expects($this->once())->method('send')->with($post);

        // $controller->handlePublish('publish', 'draft', $post);

        $this->markTestSkipped('Temporarily disabled');

    }

}