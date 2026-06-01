<?php

declare(strict_types=1);

namespace Kachuru\File;

class Link implements Path
{
    public function __construct(
        private readonly string $path
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHash(): string
    {
        return hash_file('sha256', $this->path);
    }
}
