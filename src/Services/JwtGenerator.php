<?php

namespace WritePoetry\ContentBridge\Services;


class JwtGenerator {
    public function __construct(
        private string $secret = 'your-secret',
        private int $ttl = 3600,
    ) {
    }


    /**
     * Genera un JWT.
     *
     * @param array $payload Payload aggiuntivo da includere nel token.
     * @return string Il token JWT generato.
     */
    public function generate( array $payload = array(), ?string $overrideSecret = null ): string {
        $secret = $overrideSecret ?? $this->secret;
        // Header
        $header = array(
            'alg' => 'HS256',
            'typ' => 'JWT',
        );

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));

        // Payload
        $iat = time();
        $exp = $iat + $this->ttl;

        $defaultPayload = array(
            'iat' => $iat,
            'exp' => $exp,
            'sub' => 'wordpress',
        );

        $payload          = array_merge($defaultPayload, $payload);
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        // Signature
        $signature    = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secret, true);
        $base64UrlSig = $this->base64UrlEncode($signature);

        // Token
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSig";
    }

    private function base64UrlEncode( string $data ): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
