<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk where media files will be stored.
    | This should match a disk defined in config/filesystems.php
    |
    */
    'disk' => env('DEDUP_MEDIA_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Base Directory
    |--------------------------------------------------------------------------
    |
    | The base directory within the disk where deduplicated media will be stored.
    |
    */
    'directory' => 'dedup-media',

    /*
    |--------------------------------------------------------------------------
    | Hasher Class
    |--------------------------------------------------------------------------
    |
    | The class responsible for generating content hashes.
    | Must implement SimoneBianco\LaravelDedupMedia\Contracts\Hasher
    |
    */
    'hasher' => \SimoneBianco\LaravelDedupMedia\Hashers\Sha256Hasher::class,

    /*
    |--------------------------------------------------------------------------
    | Path Generator Class
    |--------------------------------------------------------------------------
    |
    | The class responsible for generating storage paths from hashes.
    | Must implement SimoneBianco\LaravelDedupMedia\Contracts\PathGenerator
    |
    | Default uses hash-based subdirectories (ab/cd/abcdef...) to distribute
    | files evenly and avoid performance issues from too many files in one folder.
    |
    */
    'path_generator' => \SimoneBianco\LaravelDedupMedia\PathGenerators\HashBasedPathGenerator::class,
];
