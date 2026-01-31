<?php

namespace SimoneBianco\LaravelDedupMedia\PathGenerators;

use SimoneBianco\LaravelDedupMedia\Contracts\PathGenerator;

/**
 * Hash-based path generator that distributes files across subdirectories.
 * 
 * Uses the first 4 characters of the hash to create a 2-level directory structure:
 * - First 2 chars = first level directory
 * - Next 2 chars = second level directory
 * 
 * Example: hash "abcdef123..." becomes "ab/cd/abcdef123....ext"
 * 
 * This distributes files across ~65,536 possible directories,
 * preventing performance issues from too many files in a single folder.
 */
class HashBasedPathGenerator implements PathGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generate(string $hash, string $extension): string
    {
        $level1 = substr($hash, 0, 2);
        $level2 = substr($hash, 2, 2);
        
        $filename = $hash;
        
        if (!empty($extension)) {
            $filename .= '.' . ltrim($extension, '.');
        }
        
        return "{$level1}/{$level2}/{$filename}";
    }
}
