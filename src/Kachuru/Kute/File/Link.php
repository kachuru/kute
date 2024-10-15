<?php

declare(strict_types=1);

namespace Kachuru\Kute\File;

class Link
{
    public function __construct(
        private readonly string $path
    ) {
    }
}