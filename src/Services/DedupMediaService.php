<?php

namespace SimoneBianco\LaravelDedupMedia\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SimoneBianco\LaravelDedupMedia\Contracts\Hasher;
use SimoneBianco\LaravelDedupMedia\Contracts\PathGenerator;
use SimoneBianco\LaravelDedupMedia\Models\DedupMedia;

/**
 * Service for managing deduplicated media storage.
 */
class DedupMediaService
{
    public function __construct(
        protected Hasher $hasher,
        protected PathGenerator $pathGenerator,
    ) {}

    /**
     * Save media from a file path.
     *
     * @param string $sourcePath Absolute path to the source file
     * @param string|null $originalName Original filename (defaults to basename of path)
     * @param string|null $disk Storage disk (defaults to config)
     * @return DedupMedia
     */
    public function saveFromPath(
        string $sourcePath,
        ?string $originalName = null,
        ?string $disk = null
    ): DedupMedia {
        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException("Source file not found: {$sourcePath}");
        }

        $hash = $this->hasher->hashFile($sourcePath);
        $originalName = $originalName ?? basename($sourcePath);
        $disk = $disk ?? config('dedup_media.disk', 'local');

        // Check if media with this hash already exists
        $existing = DedupMedia::findByHash($hash);
        if ($existing) {
            return $existing;
        }

        // Generate storage path
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $relativePath = $this->getFullPath($hash, $extension);

        // Copy file to storage
        $storage = Storage::disk($disk);
        $stream = fopen($sourcePath, 'r');
        $storage->put($relativePath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        // Create media record
        return DedupMedia::create([
            'hash' => $hash,
            'disk' => $disk,
            'path' => $relativePath,
            'original_name' => $originalName,
            'mime_type' => mime_content_type($sourcePath) ?: null,
            'size' => filesize($sourcePath),
            'reference_count' => 0,
        ]);
    }

    /**
     * Save media from string content.
     *
     * @param string $content File content
     * @param string $originalName Original filename (used for extension)
     * @param string|null $mimeType MIME type
     * @param string|null $disk Storage disk
     * @return DedupMedia
     */
    public function saveFromContent(
        string $content,
        string $originalName,
        ?string $mimeType = null,
        ?string $disk = null
    ): DedupMedia {
        $hash = $this->hasher->hash($content);
        $disk = $disk ?? config('dedup_media.disk', 'local');

        // Check if media with this hash already exists
        $existing = DedupMedia::findByHash($hash);
        if ($existing) {
            return $existing;
        }

        // Generate storage path
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $relativePath = $this->getFullPath($hash, $extension);

        // Save content to storage
        Storage::disk($disk)->put($relativePath, $content);

        // Create media record
        return DedupMedia::create([
            'hash' => $hash,
            'disk' => $disk,
            'path' => $relativePath,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => strlen($content),
            'reference_count' => 0,
        ]);
    }

    /**
     * Save media from base64 encoded content.
     *
     * @param string $base64 Base64 encoded content
     * @param string $originalName Original filename
     * @param string|null $mimeType MIME type
     * @param string|null $disk Storage disk
     * @return DedupMedia
     */
    public function saveFromBase64(
        string $base64,
        string $originalName,
        ?string $mimeType = null,
        ?string $disk = null
    ): DedupMedia {
        $content = base64_decode($base64, true);
        
        if ($content === false) {
            throw new \InvalidArgumentException('Invalid base64 content');
        }

        return $this->saveFromContent($content, $originalName, $mimeType, $disk);
    }

    /**
     * Save media from an uploaded file.
     *
     * @param UploadedFile $file The uploaded file
     * @param string|null $disk Storage disk
     * @return DedupMedia
     */
    public function saveFromUpload(
        UploadedFile $file,
        ?string $disk = null
    ): DedupMedia {
        return $this->saveFromPath(
            $file->getRealPath(),
            $file->getClientOriginalName(),
            $disk
        );
    }

    /**
     * Check if a file with the given hash exists.
     */
    public function existsByHash(string $hash): bool
    {
        return DedupMedia::existsByHash($hash);
    }

    /**
     * Check if a file at the given path already exists in storage.
     */
    public function existsByPath(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }
        
        $hash = $this->hasher->hashFile($path);
        return $this->existsByHash($hash);
    }

    /**
     * Find media by hash.
     */
    public function findByHash(string $hash): ?DedupMedia
    {
        return DedupMedia::findByHash($hash);
    }

    /**
     * Get the full storage path with base directory.
     */
    protected function getFullPath(string $hash, string $extension): string
    {
        $baseDir = config('dedup_media.directory', 'dedup-media');
        $generatedPath = $this->pathGenerator->generate($hash, $extension);
        
        return rtrim($baseDir, '/') . '/' . ltrim($generatedPath, '/');
    }
}
