<?php

namespace WritePoetry\ContentBridge\Services;


class ImageProcessor
{
    public function __construct() {}

    /**
     * Genera un ritaglio dell'immagine.
     *
     * @param int $attachment_id ID dell'immagine.
     * @param int $height Altezza desiderata.
     * @param int $width Larghezza desiderata.
     * @return string URL dell'immagine ritagliata.
     */
    public function get_crop_url( int $attachment_id, int $width = 600, int $height = 900, string $crop_type = 'vertical' ) {
        $image_path = get_attached_file( $attachment_id );
        if ( ! file_exists( $image_path ) ) {
            return '';
        }

        $editor = wp_get_image_editor( $image_path );
        if ( is_wp_error( $editor ) ) {
            return '';
        }

        // Ritaglia l'immagine.
        $editor->resize( $width, $height, true ); // true = crop forzato
        $dest_file = $editor->generate_filename( $crop_type );
        $saved = $editor->save( $dest_file );

        if ( is_wp_error( $saved ) ) {
            return '';
        }

        // Ottieni URL.
        $upload_dir = wp_upload_dir();
        $url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $saved['path'] );
        return $url;
    }
}