<?php

declare(strict_types=1);

namespace Kachuru\Kute\Tools\Template\Ohmyzsh;

use Kachuru\Kute\Tools\Template;

class OhmyzshEditorTemplate implements Template
{
    private const NAME = 'Oh-My-Zsh editor configuration (vim)';
    private const TEMPLATE_FILE = 'ohmyzsh/editor.zsh.tmpl';
    private const TARGET_LOCATION = '~/.oh-my-zsh/custom/editor.zsh';
    private const TEMPLATE_DATA = [];
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
