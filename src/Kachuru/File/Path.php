<?php

declare(strict_types=1);

namespace Kachuru\File;

interface Path
{
    public function getPath(): string;
    public function getHash(): string;
}
