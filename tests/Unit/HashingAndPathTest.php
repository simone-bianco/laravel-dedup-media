<?php

use SimoneBianco\LaravelDedupMedia\Hashers\Sha256Hasher;
use SimoneBianco\LaravelDedupMedia\PathGenerators\HashBasedPathGenerator;

beforeEach(function () {
    $this->hasher = new Sha256Hasher();
    $this->pathGenerator = new HashBasedPathGenerator();
});

describe('Sha256Hasher', function () {
    it('hashes string content correctly', function () {
        $content = 'Hello, World!';
        $hash = $this->hasher->hash($content);
        
        expect($hash)
            ->toBe(hash('sha256', $content))
            ->toHaveLength(64);
    });

    it('produces consistent hashes for same content', function () {
        $content = 'Test content for hashing';
        
        $hash1 = $this->hasher->hash($content);
        $hash2 = $this->hasher->hash($content);
        
        expect($hash1)->toBe($hash2);
    });

    it('produces different hashes for different content', function () {
        $hash1 = $this->hasher->hash('Content A');
        $hash2 = $this->hasher->hash('Content B');
        
        expect($hash1)->not->toBe($hash2);
    });

    it('hashes files correctly', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        $content = 'File content for testing';
        file_put_contents($tempFile, $content);
        
        $hash = $this->hasher->hashFile($tempFile);
        
        expect($hash)->toBe(hash('sha256', $content));
        
        unlink($tempFile);
    });

    it('throws exception for non-existent file', function () {
        $this->hasher->hashFile('/non/existent/file.txt');
    })->throws(RuntimeException::class);
});

describe('HashBasedPathGenerator', function () {
    it('generates paths with hash-based subdirectories', function () {
        $hash = 'abcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890';
        $extension = 'pdf';
        
        $path = $this->pathGenerator->generate($hash, $extension);
        
        expect($path)->toBe('ab/cd/abcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890.pdf');
    });

    it('uses first 2 chars for level 1 directory', function () {
        $hash = 'xy' . str_repeat('0', 62);
        $path = $this->pathGenerator->generate($hash, 'txt');
        
        expect($path)->toStartWith('xy/');
    });

    it('uses chars 3-4 for level 2 directory', function () {
        $hash = 'aabb' . str_repeat('0', 60);
        $path = $this->pathGenerator->generate($hash, 'txt');
        
        expect($path)->toStartWith('aa/bb/');
    });

    it('handles extensions without leading dot', function () {
        $hash = str_repeat('a', 64);
        $path = $this->pathGenerator->generate($hash, 'jpg');
        
        expect($path)->toEndWith('.jpg');
    });

    it('handles extensions with leading dot', function () {
        $hash = str_repeat('b', 64);
        $path = $this->pathGenerator->generate($hash, '.png');
        
        expect($path)->toEndWith('.png');
    });

    it('handles empty extension', function () {
        $hash = str_repeat('c', 64);
        $path = $this->pathGenerator->generate($hash, '');
        
        expect($path)->not->toContain('.')
            ->toEndWith(str_repeat('c', 64));
    });
});
