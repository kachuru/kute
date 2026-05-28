<?php

declare(strict_types=1);

namespace Kachuru\Kute\Tools\Template;

use Kachuru\Kute\Tools\Template;

class VimTemplate implements Template
{
    private const string NAME = '`vim` configuration';
    private const string TEMPLATE_FILE = 'vimrc.tmpl';
    private const string TARGET_LOCATION = '~/.vimrc';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getFilename(): string
    {
        return self::TEMPLATE_FILE;
    }

    public function getTarget(): string
    {
        return self::TARGET_LOCATION;
    }

    public function getQuestions(): array
    {
        return [];
    }

    public function processAnswers(array $answers): array
    {
        return $answers;
    }
}
