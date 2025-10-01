<?php

declare(strict_types=1);

namespace Kachuru\Kute\Tools;

class VersionFile
{
    private const VERSION_FILE = 'versions.json';
    private array $versions = [];

    public function __construct()
    {
        if (file_exists(self::VERSION_FILE)) {
            $this->versions = json_decode(file_get_contents(self::VERSION_FILE), true);
        }
    }

    public function hasVersion(string $version): bool
    {
        return array_key_exists($version, $this->versions);
    }

    public function currentVersion(): string
    {
        $versions = array_keys($this->versions);
        natsort($versions);
        return array_pop($versions);
    }

    public function addVersion(string $newVersion, string $file, string $algo): string
    {
        $fileHash = hash_file($algo, $file);

        $this->versions[$newVersion] = [
            'version' => $newVersion,
            $algo => $fileHash
        ];

        file_put_contents(self::VERSION_FILE, json_encode($this->versions, JSON_PRETTY_PRINT));

        return $fileHash;
    }
}