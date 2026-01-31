<?php

namespace SimoneBianco\LaravelDedupMedia\Contracts;

/**
 * Contract for path generation implementations.
 */
interface PathGenerator
{
    /**
     * Generate a storage path from a hash and file extension.
     *
     * @param string $hash The content hash
     * @param string $extension The file extension (without leading dot)
     * @return string The relative storage path
     */
    public function generate(string $hash, string $extension): string;
}
