<?php

declare(strict_types=1);

namespace Kachuru\Kute\File;

class Directory
{
    const UNIX_TRAVERSE_DIRECTORIES = ['.', '..'];

    private array $contents;

    public function __construct(
        private readonly string $path
    ) {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('Invalid path');
        }
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

    private function parse(): void
    {
        $dir = Dir($this->path);
        while (false !== ($entry = $dir->read())) {
            if (in_array($entry, self::UNIX_TRAVERSE_DIRECTORIES)) {
                continue;
            }

            $fullEntry = realpath($this->path . DIRECTORY_SEPARATOR . $entry);

            if (is_link($fullEntry)) {
                $this->contents[$fullEntry] = new Link($fullEntry);
            }

            if (is_file($fullEntry)) {
                $this->contents[$fullEntry] = new File($fullEntry);
            }

            if (is_dir($fullEntry)) {
                $this->contents[$fullEntry] = new Directory($fullEntry);
            }
        }
    }
}