<?php

namespace SimoneBianco\LaravelDedupMedia\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

/**
 * Represents a deduplicated media file.
 *
 * @property string $id UUID primary key
 * @property string $hash SHA256 hash of file content
 * @property string $disk Storage disk name
 * @property string $path Relative path within the disk
 * @property string $original_name Original filename
 * @property string|null $mime_type MIME type
 * @property int $size File size in bytes
 * @property int $reference_count Number of models referencing this media
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class DedupMedia extends Model
{
    use HasUuids;

    protected $table = 'dedup_media';

    protected $guarded = [];

    protected $fillable = [
        'hash',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'reference_count',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'reference_count' => 'integer',
        ];
    }

    /**
     * Find a media by its content hash.
     */
    public static function findByHash(string $hash): ?static
    {
        return static::where('hash', $hash)->first();
    }

    /**
     * Check if a media with the given hash exists.
     */
    public static function existsByHash(string $hash): bool
    {
        return static::where('hash', $hash)->exists();
    }

    /**
     * Increment the reference count.
     */
    public function incrementReference(): static
    {
        $this->increment('reference_count');
        return $this;
    }

    /**
     * Decrement the reference count and delete if zero.
     * 
     * @return bool True if the media was deleted, false otherwise
     */
    public function decrementReference(): bool
    {
        $this->decrement('reference_count');
        $this->refresh();

        if ($this->reference_count <= 0) {
            return $this->deleteWithFile();
        }

        return false;
    }

    /**
     * Delete the media record and its physical file.
     */
    public function deleteWithFile(): bool
    {
        $disk = Storage::disk($this->disk);
        
        if ($disk->exists($this->path)) {
            $disk->delete($this->path);
        }

        return $this->delete();
    }

    /**
     * Get the full storage path.
     */
    public function getFullPath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    /**
     * Get the public URL (if disk supports it).
     */
    public function getUrl(): ?string
    {
        try {
            return Storage::disk($this->disk)->url($this->path);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Get the file contents.
     */
    public function getContents(): string
    {
        return Storage::disk($this->disk)->get($this->path);
    }

    /**
     * Stream the file contents.
     *
     * @return resource|null
     */
    public function getStream()
    {
        return Storage::disk($this->disk)->readStream($this->path);
    }
}
