<?php

namespace SimoneBianco\LaravelDedupMedia\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DedupMediable extends Model
{
    use HasUuids;

    protected $table = 'dedup_mediables';

    protected $guarded = [];

    protected $fillable = [
        'dedup_media_id',
        'mediable_type',
        'mediable_id',
        'collection',
    ];
}
