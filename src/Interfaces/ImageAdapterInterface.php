<?php

namespace WritePoetry\ContentBridge\Interfaces;

interface ImageAdapterInterface {
    /**
     * Recupera i dati dell'immagine in evidenza di un post.
     *
     * @param int $postId
     * @return array|null
     */
    public function getFeaturedImageData( int $postId ): ?array;
}
