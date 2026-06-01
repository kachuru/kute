<?php

declare(strict_types=1);

namespace Kachuru\File;

class Directory implements Path
{
    private const array UNIX_TRAVERSE_DIRECTORIES = ['.', '..'];

    private array $contents;

    public function __construct(
        private readonly string $path
    ) {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('Invalid path');
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHash(): string
    {
        return hash_file('sha256', $this->path);
    }

    public function getContents(): array
    {
        if (!isset($this->contents)) {
            $this->parse();
        }

        return $this->contents;
    }

    public function isEmpty(): bool
    {
        if (!isset($this->contents)) {
            $this->parse();
        }

        return empty($this->contents);
    }

    public function delete(Path $entry): bool
    {
        $result = $entry instanceof Directory
            ? rmdir($entry->getPath())
            : unlink($entry->getPath());

        unset($this->contents[$this->hashFilename($entry->getPath())]);

        return $result;
    }

    private function parse(): void
    {
        $this->contents = [];

        $dir = Dir($this->path);
        while (false !== ($entry = $dir->read())) {
            if (in_array($entry, self::UNIX_TRAVERSE_DIRECTORIES)) {
                continue;
            }

            $fullEntry = realpath($this->path . DIRECTORY_SEPARATOR . $entry);

            $this->contents[$this->hashFilename($fullEntry)] = match (true) {
                is_link($fullEntry) => new Link($fullEntry),
                is_file($fullEntry) => new File($fullEntry),
                is_dir($fullEntry) => new Directory($fullEntry),
                default => throw new \InvalidArgumentException('Unexpected directory entry: ' . $entry),
            };
        }
    }

    private function hashFilename(string $entry): string
    {
        return hash('sha256', $entry);
    }
}
