<?php

namespace SimoneBianco\LaravelDedupMedia\Contracts;

/**
 * Contract for content hashing implementations.
 */
interface Hasher
{
    /**
     * Generate a hash from string content.
     *
     * @param string $content The content to hash
     * @return string The generated hash
     */
    public function hash(string $content): string;

    /**
     * Generate a hash from a file's contents.
     *
     * @param string $path Absolute path to the file
     * @return string The generated hash
     * @throws \RuntimeException If the file cannot be read
     */
    public function hashFile(string $path): string;
}
