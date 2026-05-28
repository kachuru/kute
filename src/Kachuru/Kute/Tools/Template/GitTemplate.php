<?php

declare(strict_types=1);

namespace Kachuru\Kute\Tools\Template;

use Kachuru\Kute\Tools\Template;

class GitTemplate implements Template
{
    private const string NAME = '`git` configuration';
    private const string TEMPLATE_FILE = 'gitconfig.tmpl';
    private const string TARGET_LOCATION = '~/.gitconfig';
    private const array TEMPLATE_DATA = [
        'name' => 'Please enter your full name, as you would like it to appear in the git history',
        'email' => 'Please enter your email address',
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
