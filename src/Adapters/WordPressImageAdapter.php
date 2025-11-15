<?php

namespace WritePoetry\ContentBridge\Adapters;

use WritePoetry\ContentBridge\Interfaces\ImageAdapterInterface;
use WritePoetry\ContentBridge\Services\ImageProcessor;

class WordPressImageAdapter implements ImageAdapterInterface
{
    public function __construct(private ImageProcessor $imageProcessor)
    {
    }


    public function getFeaturedImageData(int $postId): ?array
    {
        // Fallback to default featured image ID if not set.
        $imageId = get_post_thumbnail_id($postId)
            ?: apply_filters('writepoetry_contentbridge_default_featured_image', null);

        // If no image ID is found, return null.
        if (!is_int($imageId) || $imageId <= 0) {
            return [];
        }

        $sizes = [
            'full'      => get_the_post_thumbnail_url($postId, 'full'),
            'large'     => get_the_post_thumbnail_url($postId, 'large'),
            'medium'    => get_the_post_thumbnail_url($postId, 'medium'),
            'thumbnail' => get_the_post_thumbnail_url($postId, 'thumbnail'),
        ];

        // Add vertical crop
        $sizes['vertical'] = $this->imageProcessor->getUrlFromId($imageId, 'vertical');

        $imageData = wp_get_attachment_metadata($imageId);

        return array(
            'id'      => $imageId,
            'url'     => get_the_post_thumbnail_url($postId, 'full'),
            'alt'     => get_post_meta($imageId, '_wp_attachment_image_alt', true),
            'caption' => wp_get_attachment_caption($imageId),
            'width'   => $imageData['width'] ?? null,
            'height'  => $imageData['height'] ?? null,
            'sizes'   => $sizes,
        );
    }
}
