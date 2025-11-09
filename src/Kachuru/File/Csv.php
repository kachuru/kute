<?php

declare(strict_types=1);

namespace Kachuru\File;

use Generator;

class Csv
{
    /** @var resource */
    private $fh;

    private array $headers;

    public function __construct(string $filename, bool $headerRow = false)
    {
        if (!strstr($filename, 'http')) {
            if (!file_exists($filename)) {
                throw new \InvalidArgumentException(sprintf('File not found: %s', $filename));
            }

            if (!is_readable($filename)) {
                throw new \InvalidArgumentException(sprintf('File is not readable: %s', $filename));
            }
        }

        $this->fh = fopen($filename, 'r');

        if ($headerRow) {
            $this->headers = fgetcsv($this->fh);
        }
    }

    public function getRow(): Generator|bool
    {
        $row = fgetcsv($this->fh);
        if ($row === false) {
            return false;
        }

        if (!empty($this->headers)) {
            yield array_combine($this->headers, $row);
        }

        yield $row;
    }
}
