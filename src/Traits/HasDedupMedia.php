<?php

namespace SimoneBianco\LaravelDedupMedia\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\UploadedFile;
use SimoneBianco\LaravelDedupMedia\Facades\DedupMedia as DedupMediaFacade;
use SimoneBianco\LaravelDedupMedia\Models\DedupMedia;

/**
 * Trait for models that can have deduplicated media attached.
 * 
 * Provides automatic reference count decrement on model deletion.
 */
trait HasDedupMedia
{
    /**
     * Boot the trait.
     * Registers event listeners for automatic cleanup on delete.
     */
    public static function bootHasDedupMedia(): void
    {
        static::deleting(function ($model) {
            $model->detachAllMedia();
        });
    }

    /**
     * Get all deduplicated media for this model.
     */
    public function dedupMedia(): MorphToMany
    {
        return $this->morphToMany(
            DedupMedia::class,
            'mediable',
            'dedup_mediables',
            'mediable_id',
            'dedup_media_id'
        )->withPivot('collection')->withTimestamps();
    }

    /**
     * Get media for a specific collection.
     *
     * @param string $collection
     * @return \Illuminate\Database\Eloquent\Collection<int, DedupMedia>
     */
    public function getMedia(string $collection = 'default'): \Illuminate\Database\Eloquent\Collection
    {
        return $this->dedupMedia()->wherePivot('collection', $collection)->get();
    }

    /**
     * Get the first media item for a collection.
     */
    public function getFirstMedia(string $collection = 'default'): ?DedupMedia
    {
        return $this->dedupMedia()->wherePivot('collection', $collection)->first();
    }

    /**
     * Attach media from a file path.
     *
     * @param string $path Absolute path to the file
     * @param string $collection Collection name
     * @param string|null $originalName Original filename (defaults to basename)
     * @return DedupMedia
     */
    public function attachMediaFromPath(
        string $path,
        string $collection = 'default',
        ?string $originalName = null
    ): DedupMedia {
        $media = DedupMediaFacade::saveFromPath($path, $originalName);
        
        return $this->attachMedia($media, $collection);
    }

    /**
     * Attach media from string content.
     *
     * @param string $content File content
     * @param string $originalName Original filename
     * @param string $collection Collection name
     * @param string|null $mimeType MIME type
     * @return DedupMedia
     */
    public function attachMediaFromContent(
        string $content,
        string $originalName,
        string $collection = 'default',
        ?string $mimeType = null
    ): DedupMedia {
        $media = DedupMediaFacade::saveFromContent($content, $originalName, $mimeType);
        
        return $this->attachMedia($media, $collection);
    }

    /**
     * Attach media from base64 encoded content.
     *
     * @param string $base64 Base64 encoded content
     * @param string $originalName Original filename
     * @param string $collection Collection name
     * @param string|null $mimeType MIME type
     * @return DedupMedia
     */
    public function attachMediaFromBase64(
        string $base64,
        string $originalName,
        string $collection = 'default',
        ?string $mimeType = null
    ): DedupMedia {
        $media = DedupMediaFacade::saveFromBase64($base64, $originalName, $mimeType);
        
        return $this->attachMedia($media, $collection);
    }

    /**
     * Attach media from an uploaded file.
     *
     * @param UploadedFile $file The uploaded file
     * @param string $collection Collection name
     * @return DedupMedia
     */
    public function attachMediaFromUpload(
        UploadedFile $file,
        string $collection = 'default'
    ): DedupMedia {
        $media = DedupMediaFacade::saveFromUpload($file);
        
        return $this->attachMedia($media, $collection);
    }

    /**
     * Attach an existing DedupMedia to this model.
     *
     * @param DedupMedia $media The media to attach
     * @param string $collection Collection name
     * @return DedupMedia
     */
    public function attachMedia(DedupMedia $media, string $collection = 'default'): DedupMedia
    {
        // Check if already attached to this model in this collection
        $existing = $this->dedupMedia()
            ->where('dedup_media.id', $media->id)
            ->wherePivot('collection', $collection)
            ->first();

        if (!$existing) {
            $this->dedupMedia()->attach($media->id, ['collection' => $collection]);
            $media->incrementReference();
        }

        return $media;
    }

    /**
     * Detach media from this model.
     *
     * @param DedupMedia $media The media to detach
     * @param string|null $collection Specific collection, or null for all
     */
    public function detachMedia(DedupMedia $media, ?string $collection = null): void
    {
        $query = $this->dedupMedia()->where('dedup_media.id', $media->id);
        
        if ($collection !== null) {
            $query->wherePivot('collection', $collection);
        }

        $count = $query->count();
        
        if ($count > 0) {
            if ($collection !== null) {
                $this->dedupMedia()->wherePivot('collection', $collection)->detach($media->id);
            } else {
                $this->dedupMedia()->detach($media->id);
            }
            
            // Decrement reference for each collection detached
            for ($i = 0; $i < $count; $i++) {
                $media->decrementReference();
            }
        }
    }

    /**
     * Detach all media from this model.
     * Called automatically on model delete.
     */
    public function detachAllMedia(): void
    {
        $this->dedupMedia->each(function (DedupMedia $media) {
            $media->decrementReference();
        });
        
        $this->dedupMedia()->detach();
    }

    /**
     * Check if media exists for a collection.
     */
    public function hasMedia(string $collection = 'default'): bool
    {
        return $this->dedupMedia()->wherePivot('collection', $collection)->exists();
    }

    /**
     * Get media count for a collection.
     */
    public function getMediaCount(string $collection = 'default'): int
    {
        return $this->dedupMedia()->wherePivot('collection', $collection)->count();
    }
}
