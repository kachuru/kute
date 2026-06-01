<?php

declare(strict_types=1);

namespace Kachuru\File;

use Stringable;

class File implements Path, Stringable
{
    private string $hash;

    public function __construct(
        private readonly string $path
    ) {
    }

    public function __toString(): string
    {
        return $this->path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHash(): string
    {
        if (!isset($this->hash)) {
            $this->hash = hash_file('sha256', $this->path);
        }

        return $this->hash;
    }
}
