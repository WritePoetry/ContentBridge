<?php


namespace WritePoetry\ContentBridge\Config;

use WritePoetry\ContentBridge\Environment\EnvironmentInterface;

class PluginConfig {
    private array $config;

    public function __construct(private EnvironmentInterface $env) {
        $this->config = [
            'n8n_jwt_secret' => $this->require('N8N_JWT_SECRET'),
            'n8n_webhook_url' => $this->require('N8N_WEBHOOK_URL'),
            'webapp_url' => $this->require('WEB_APP_URL'),
            'webapp_token' => $this->require('WEB_APP_TOKEN'),
        ];
    }

    private function require(string $key): string {
        $value = $this->env->get($key);
        if (!$value) {
            throw new \RuntimeException($key.' must be defined in the environment or wp-config.php.');
        }
        return $value;
    }


    public function get( string $key ): string {
        if (!isset($this->config[$key])) {
            throw new \InvalidArgumentException('Config key ' . $key . ' not found');
        }
    
        return $this->config[$key];
    }
}
