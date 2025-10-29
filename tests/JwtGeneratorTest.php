<?php
/**
 * Class SampleTest
 *
 * @package ContentBridge
 */

namespace WritePoetry\ContentBridge\Tests;

use PHPUnit\Framework\TestCase;
use WritePoetry\ContentBridge\Services\JwtGenerator;
use WritePoetry\ContentBridge\Tests\Environment\TestEnvironment;


/**
 * Sample test case.
 */
class JwtGeneratorTest extends TestCase {
    private string $secret;
    private TestEnvironment $env;
    private string $defaultSecret = 'default-secret';

    protected function setUp(): void{
        parent::setUp();

        $this->env = new TestEnvironment([
            'N8N_JWT_SECRET' => 'fallback-secret',
        ]);
    }

    /**
     * @dataProvider jwtSecretProvider
     * Test that JwtGenerator::generate returns a valid JWT string.
     * @param ?string $customSecret
     * @return void
     */
	public function test_generate_returns_string(?string $customSecret): void {
        $jwt = new JwtGenerator($this->defaultSecret, 3600);

        $token = $customSecret
            ? $jwt->generate([], $customSecret)
            : $jwt->generate();

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
	
		$this->assertStringMatchesFormat('%s.%s.%s', $token, 'The token should contains exactly two dots, ensuring it has the three-part JWT structure.');
	}


    /**
     * Test that JwtGenerator::generate includes custom payload data.
     * @return void
     */
    public function test_generate_includes_custom_payload() {
        $jwt = new JwtGenerator($this->env->get('N8N_JWT_SECRET'), 3600);
        
        $token = $jwt->generate(['foo' => 'bar']);

        [$headerB64, $payloadB64, $sigB64] = explode('.', $token);
        $decodedPayload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);

        $this->assertArrayHasKey('foo', $decodedPayload);
        $this->assertSame('bar', $decodedPayload['foo']);
    }

    public function test_encode_base64Url() {
        $jwt = new JwtGenerator($this->env->get('N8N_JWT_SECRET'), 3600);
        
        $method = new \ReflectionMethod(JwtGenerator::class, 'base64UrlEncode');
        $method->setAccessible(true);

        $result = $method->invokeArgs($jwt, ['test data']);
        $this->assertSame('dGVzdCBkYXRh', $result);
    }

    /**
     * Data provider for test_generate_returns_string.
     * 
     * @return array
     */
	public static function jwtSecretProvider(): iterable {
		return [
            'default secret' => [null],
            'custom secret' => ['custom-secret'], 
		];
	}
}
