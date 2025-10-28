<?php


namespace WritePoetry\ContentBridge\Config;

class PluginConfig {
    private array $config;

    public function __construct() {
        $this->config = [
            'n8n_jwt_secret' => $this->getEnvOrConstant('N8N_JWT_SECRET'),
            'n8n_webhook_url' => $this->getEnvOrConstant('N8N_WEBHOOK_URL'),
            'webapp_url' => $this->getEnvOrConstant('WEB_APP_URL'),
            'webapp_token' => $this->getEnvOrConstant('WEB_APP_TOKEN'),
        ];
    }

    private function getEnvOrConstant(string $key): string {
        $value = defined($key) ? constant($key) : getenv($key);
        if (!$value) {
            throw new \RuntimeException("$key must be defined in wp-config.php or environment.");
        }
        return $value;
    }

    public function get(string $key): string {
        return $this->config[$key] ?? throw new \InvalidArgumentException("Config key '$key' not found");
    }
}
