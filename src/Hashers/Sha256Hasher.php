<?php

namespace SimoneBianco\LaravelDedupMedia\Hashers;

use SimoneBianco\LaravelDedupMedia\Contracts\Hasher;

/**
 * SHA256 hasher implementation.
 */
class Sha256Hasher implements Hasher
{
    /**
     * {@inheritdoc}
     */
    public function hash(string $content): string
    {
        return hash('sha256', $content);
    }

    /**
     * {@inheritdoc}
     */
    public function hashFile(string $path): string
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        $hash = hash_file('sha256', $path);

        if ($hash === false) {
            throw new \RuntimeException("Failed to calculate hash for file: {$path}");
        }

        return $hash;
    }
}
