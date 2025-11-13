<?php

/**
 * Class PluginConfigTest
 *
 * @package ContentBridge
 */

namespace WritePoetry\ContentBridge\Tests;

use PHPUnit\Framework\TestCase;
use WritePoetry\ContentBridge\Config\PluginConfig;
use WritePoetry\ContentBridge\Tests\Environment\TestEnvironment;

/**
 * Sample test case.
 */
class PluginConfigTest extends TestCase
{
    private TestEnvironment $env;

    protected function setUp(): void
    {
        parent::setUp();

        $this->env = new TestEnvironment([
            'N8N_JWT_SECRET' => 'secret',
            'N8N_WEBHOOK_URL' => 'https://example.com',
            'WEB_APP_URL' => 'https://app.example.com',
            'WEB_APP_TOKEN' => 12345,
        ]);
    }

    /**
     * @dataProvider configKeysProvider
     *
     * Test that PluginConfig::get returns the correct value from the environment.
     *
     * @param string $key
     * @param mixed $expectedValue
     * @return void
     */
    public function test_get_returns_correct_value_from_environment(string $key, mixed $expectedValue): void
    {
        $config = new PluginConfig($this->env);


        $this->assertEquals($expectedValue, $config->get($key));
        // $this->assertSame('https://example.com', $config->get('n8n_webhook_url'));
    }

    public static function configKeysProvider(): iterable
    {
        return [
            'String value' => [
                'key'   => 'n8n_jwt_secret',
                'expectedValue' => 'secret',
            ],
            'Numeric value' => [
                'key'   => 'webapp_token',
                'expectedValue' => 12345,
            ]
        ];
    }
}
