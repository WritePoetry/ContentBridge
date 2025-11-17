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
            'BREVO_LIST_IDS' => 1,
            'BREVO_SENDER_ID' => 1,
            'BREVO_TEMPLATE_ID' => 1
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
    }

    public static function configKeysProvider(): iterable
    {
        return [
            'String value' => [
                'key'   => 'brevo_template',
                'expectedValue' => 1,
            ],
            'Numeric value' => [
                'key'   => 'brevo_sender',
                'expectedValue' => 1,
            ]
        ];
    }
}
