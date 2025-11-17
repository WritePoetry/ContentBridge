<?php

namespace WritePoetry\ContentBridge\Factories;

use WritePoetry\ContentBridge\Interfaces\ImageAdapterInterface;
use WritePoetry\ContentBridge\Services\ImageProcessor;
use WritePoetry\ContentBridge\Config\PluginConfig;

class WebhookPayloadFactory
{
    public function __construct(
        private PluginConfig $config,
        private ImageAdapterInterface $imageAdapter,
        private ImageProcessor $imageProcessor,
    ) {
    }

    public function make(\WP_Post $post): array
    {
        return array(
            'post'   => array(
                'post_title'     => $post->post_title,
                'post_content'   => trim(htmlspecialchars(apply_filters('the_content', $post->post_content))),
                'post_excerpt'   => $post->post_excerpt ?: wp_trim_words($post->post_content, 30, '…'),
                'post_url'       => get_permalink($post->ID),
                'post_date'      => $post->post_date,
                'post_id'        => $post->ID,
                'meta_desc'      => get_post_meta($post->ID, '_yoast_wpseo_metadesc', true),
                'featured_image' => $this->imageAdapter->getFeaturedImageData($post->ID),
            ),
            'site'   => array(
                'name'        => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'url'         => get_bloginfo('url'),
                'admin_email' => get_bloginfo('admin_email'),
            ),
            'author' => array(
                'name'  => get_the_author_meta('display_name', $post->post_author),
                'email' => get_the_author_meta('user_email', $post->post_author),
            ),
            'brevo'  => array(
                'list_ids'    => $this->config->get('brevo_lists') ?? array(),
                'sender_id'   => (int) $this->config->get('brevo_sender'),
                'template_id' => (int) $this->config->get('brevo_template') ?? null,
            ),
        );
    }
}
