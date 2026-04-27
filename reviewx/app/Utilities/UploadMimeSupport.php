<?php

namespace ReviewX\Utilities;

\defined('ABSPATH') || exit;
class UploadMimeSupport
{
    private static int $activeScopes = 0;
    public static function bootstrapGlobalHooks() : void
    {
        \add_filter('upload_mimes', [self::class, 'filterUploadMimes']);
        \add_filter('wp_check_filetype_and_ext', [self::class, 'fixSvgFiletype'], 10, 4);
        \add_filter('wp_prepare_attachment_for_js', [self::class, 'prepareAttachmentForJs'], 10, 3);
    }
    public static function withAllowedUploads(callable $callback)
    {
        self::enable();
        try {
            return $callback();
        } finally {
            self::disable();
        }
    }
    public static function getAllowedUploadMimes() : array
    {
        return ['jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml', 'mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg'];
    }
    public static function getAllowedAttachmentMimeTypesForValidation() : array
    {
        return \array_values(\array_unique(\array_values(self::getAllowedUploadMimes())));
    }
    public static function isAllowedAttachmentFile(string $fileName = '', string $mimeType = '') : bool
    {
        $mimeType = \strtolower(\trim($mimeType));
        if ($mimeType !== '' && \in_array($mimeType, self::getAllowedAttachmentMimeTypesForValidation(), \true)) {
            return \true;
        }
        if ($fileName === '') {
            return \false;
        }
        $fileType = \wp_check_filetype($fileName, self::getAllowedUploadMimes());
        return !empty($fileType['type']);
    }
    public static function getWpHandleUploadOverrides(array $overrides = []) : array
    {
        return \array_merge(['test_form' => \false, 'mimes' => self::getAllowedUploadMimes()], $overrides);
    }
    public static function generateAttachmentMetadata(int $attachmentId, string $filePath, ?string $mimeType = null) : array
    {
        $mimeType = \is_string($mimeType) ? $mimeType : '';
        if (self::isSvgMimeType($mimeType) || self::hasSvgExtension($filePath)) {
            return [];
        }
        $metadata = \wp_generate_attachment_metadata($attachmentId, $filePath);
        return \is_array($metadata) ? $metadata : [];
    }
    public static function filterUploadMimes(array $mimes) : array
    {
        return \array_merge($mimes, self::getAllowedUploadMimes());
    }
    public static function fixSvgFiletype($data, $file, $filename, $mimes)
    {
        unset($file, $mimes);
        if (!\is_string($filename)) {
            return $data;
        }
        $extension = \strtolower((string) \pathinfo($filename, \PATHINFO_EXTENSION));
        if ($extension === 'svg' || $extension === 'svgz') {
            $data['ext'] = $extension;
            $data['type'] = 'image/svg+xml';
            $data['proper_filename'] = $filename;
        }
        if ($extension === 'webp') {
            $data['ext'] = 'webp';
            $data['type'] = 'image/webp';
            $data['proper_filename'] = $filename;
        }
        return $data;
    }
    public static function prepareAttachmentForJs(array $response, $attachment, $meta) : array
    {
        unset($meta);
        if (!isset($attachment->ID)) {
            return $response;
        }
        $mimeType = (string) \get_post_mime_type($attachment->ID);
        if (!self::isSvgMimeType($mimeType)) {
            return $response;
        }
        $url = \wp_get_attachment_url($attachment->ID);
        if (!$url) {
            return $response;
        }
        $response['url'] = $url;
        $response['icon'] = $url;
        $response['type'] = 'image';
        $response['subtype'] = 'svg+xml';
        $response['mime'] = 'image/svg+xml';
        $response['filename'] = $response['filename'] ?? \basename((string) \get_attached_file($attachment->ID));
        $response['sizes'] = $response['sizes'] ?? [];
        $response['sizes']['full'] = ['url' => $url, 'width' => $response['width'] ?? 0, 'height' => $response['height'] ?? 0, 'orientation' => 'landscape'];
        return $response;
    }
    private static function enable() : void
    {
        if (self::$activeScopes === 0) {
            \add_filter('upload_mimes', [self::class, 'filterUploadMimes']);
            \add_filter('wp_check_filetype_and_ext', [self::class, 'fixSvgFiletype'], 10, 4);
        }
        self::$activeScopes++;
    }
    private static function disable() : void
    {
        if (self::$activeScopes <= 0) {
            self::$activeScopes = 0;
            return;
        }
        self::$activeScopes--;
        if (self::$activeScopes === 0) {
            \remove_filter('upload_mimes', [self::class, 'filterUploadMimes']);
            \remove_filter('wp_check_filetype_and_ext', [self::class, 'fixSvgFiletype'], 10);
        }
    }
    private static function isSvgMimeType(string $mimeType) : bool
    {
        return \strtolower($mimeType) === 'image/svg+xml';
    }
    private static function hasSvgExtension(string $filePath) : bool
    {
        $extension = \strtolower((string) \pathinfo($filePath, \PATHINFO_EXTENSION));
        return $extension === 'svg' || $extension === 'svgz';
    }
}
