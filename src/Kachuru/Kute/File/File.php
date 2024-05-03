<?php

declare(strict_types=1);

namespace Kachuru\Kute\File;

class File
{
    public function __construct(
        private readonly string $path
    ) {
    }
}