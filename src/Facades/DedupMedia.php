<?php

namespace SimoneBianco\LaravelDedupMedia\Facades;

use Illuminate\Support\Facades\Facade;
use SimoneBianco\LaravelDedupMedia\Models\DedupMedia as DedupMediaModel;
use SimoneBianco\LaravelDedupMedia\Services\DedupMediaService;

/**
 * @method static DedupMediaModel saveFromPath(string $sourcePath, ?string $originalName = null, ?string $disk = null)
 * @method static DedupMediaModel saveFromContent(string $content, string $originalName, ?string $mimeType = null, ?string $disk = null)
 * @method static DedupMediaModel saveFromBase64(string $base64, string $originalName, ?string $mimeType = null, ?string $disk = null)
 * @method static DedupMediaModel saveFromUpload(\Illuminate\Http\UploadedFile $file, ?string $disk = null)
 * @method static bool existsByHash(string $hash)
 * @method static bool existsByPath(string $path)
 * @method static DedupMediaModel|null findByHash(string $hash)
 *
 * @see \SimoneBianco\LaravelDedupMedia\Services\DedupMediaService
 */
class DedupMedia extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'dedup-media';
    }
}
