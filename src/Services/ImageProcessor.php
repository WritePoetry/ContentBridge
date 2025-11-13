<?php

namespace WritePoetry\ContentBridge\Services;

class ImageProcessor
{
    public function __construct()
    {
    }

    /**
     * Genera un ritaglio dell'immagine.
     *
     * @param int $attachmentId ID dell'immagine.
     * @param int $height Altezza desiderata.
     * @param int $width Larghezza desiderata.
     * @return string path completo del file appena creato sul filesystem.
     */
    public function cropImage(
        int $attachmentId,
        int $width = 600,
        int $height = 900,
        string $cropType = 'vertical'
    ): string {
        if (! wp_attachment_is_image($attachmentId)) {
            return '';
        }

        $imagePath = get_attached_file($attachmentId);
        if (! file_exists($imagePath)) {
            return '';
        }

        $metadata = wp_get_attachment_metadata($attachmentId);

        // Nel caso in cui ci sia già il crop, lo restituisce direttamente.
        if (isset($metadata['sizes'][ $cropType ])) {
            $existingFile = path_join(
                dirname($imagePath),
                $metadata['sizes'][ $cropType ]['file']
            );

            if (file_exists($existingFile)) {
                return $existingFile;
            }
        }

        $editor = wp_get_image_editor($imagePath);
        if (is_wp_error($editor)) {
            return '';
        }

        // Ritaglia l'immagine.
        $editor->resize($width, $height, true); // true = crop forzato.

        // Calcola il percorso finale del crop.
        $destFile = $editor->generate_filename($cropType);
        $saved    = $editor->save($destFile);

        if (is_wp_error($saved)) {
            return '';
        }

        if (! isset($metadata['sizes'])) {
            $metadata['sizes'] = array();
        }

        // Aggiunge o aggiorna la nuova size nei metadata.
        $metadata['sizes'][ $cropType ] = array(
            'file'      => basename($destFile),
            'width'     => $width,
            'height'    => $height,
            'mime-type' => $saved['mime-type'],
        );

        wp_update_attachment_metadata($attachmentId, $metadata);

        return $saved['path'];
    }

    /**
     * Ottiene l'URL dell'immagine dal percorso del file.
     *
     * @param string $filePath Percorso completo del file.
     * @return string URL dell'immagine.
     */
    public function getUrlFromPath(string $filePath): string
    {
        // Ottieni URL.
        $uploadDir = wp_upload_dir();
        return str_replace($uploadDir['basedir'], $uploadDir['baseurl'], $filePath);
    }

    public function getUrlFromId(int $attachmentId, string $cropType): string
    {
        $url = wp_get_attachment_image_src($attachmentId, $cropType)[0];
        if (! $url) {
            return '';
        }
        return $url;
    }

    /**
     * Ritaglia l'immagine e restituisce l'URL.
     *
     * @param int $attachmentId ID dell'immagine.
     * @param int $height Altezza desiderata.
     * @param int $width Larghezza desiderata.
     * @return string URL dell'immagine ritagliata.
     */
    public function cropImageAndGetUrl(
        int $attachmentId,
        int $width = 600,
        int $height = 900,
        string $cropType = 'vertical'
    ): string {
        $path = $this->cropImage($attachmentId, $width, $height, $cropType);
        if (! $path) {
            return '';
        }

        return $this->getUrlFromPath($path);
    }
}
