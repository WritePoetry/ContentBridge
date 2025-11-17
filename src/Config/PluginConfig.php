<?php

namespace WritePoetry\ContentBridge\Config;

use WritePoetry\ContentBridge\Environment\EnvironmentInterface;

class PluginConfig
{
    private array $config;

    public function __construct(private EnvironmentInterface $env)
    {
        // mandatory keys
        $this->config = [
            // 'example' => $this->require('EXAMPLE'),
        ];

        // optional keys
        $this->config['brevo_lists'] = $this->env->get('BREVO_LIST_IDS') ?? null;
        $this->config['brevo_sender'] = $this->env->get('BREVO_SENDER_ID') ?? null;
        $this->config['brevo_template'] = $this->env->get('BREVO_TEMPLATE_ID') ?? null;
    }

    private function require(string $key): string
    {
        $value = $this->env->get($key);
        if (!$value) {
            throw new \RuntimeException($key . ' must be defined in the environment or wp-config.php.');
        }
        return $value;
    }


    public function get(string $key): string
    {
        if (!isset($this->config[$key])) {
            throw new \InvalidArgumentException('Config key ' . $key . ' not found');
        }

        return $this->config[$key];
    }
}
