<?php
/**
 * Class SampleTest
 *
 * @package ContentBridge
 */

namespace WritePoetry\ContentBridge\Tests;

use PHPUnit\Framework\TestCase;
use WritePoetry\ContentBridge\Services\JwtGenerator;

/**
 * Sample test case.
 */
class JwtGeneratorTest extends TestCase {
    private string $secret;

    protected function setUp(): void{
        parent::setUp();
        $this->secret = $_ENV['N8N_JWT_SECRET'] ?? getenv('N8N_JWT_SECRET') ?? 'fallback-secret';
    }

	public function test_generate_returns_string() {
        $jwt = new JwtGenerator($this->secret, 3600);
        $token = $jwt->generate();
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
	
		$this->assertStringMatchesFormat('%s.%s.%s', $token, 'The token should contains exactly two dots, ensuring it has the three-part JWT structure.');
	}

    public function test_generate_includes_custom_payload() {
        $jwt = new JwtGenerator($this->secret, 3600);
        
        $token = $jwt->generate(['foo' => 'bar']);

        [$headerB64, $payloadB64, $sigB64] = explode('.', $token);
        $decodedPayload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);

        $this->assertArrayHasKey('foo', $decodedPayload);
        $this->assertSame('bar', $decodedPayload['foo']);
    }

    public function test_encode_base64Url() {
        $jwt = new JwtGenerator($this->secret, 3600);
        
        $method = new \ReflectionMethod(JwtGenerator::class, 'base64UrlEncode');
        $method->setAccessible(true);

        $result = $method->invokeArgs($jwt, ['test data']);
        $this->assertSame('dGVzdCBkYXRh', $result);
    }
}
