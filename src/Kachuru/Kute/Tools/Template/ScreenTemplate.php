<?php

declare(strict_types=1);

namespace Kachuru\Kute\Tools\Template;

use Kachuru\Kute\Tools\Template;

class ScreenTemplate implements Template
{
    private const NAME = '`screen` configuration';
    private const TEMPLATE_FILE = 'screenrc.tmpl';
    private const TARGET_LOCATION = '~/.screenrc';
    private const TEMPLATE_DATA = [
    ];

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
        return self::TEMPLATE_DATA;
    }

    public function processAnswers(array $answers): array
    {
        return $answers;
    }
}
