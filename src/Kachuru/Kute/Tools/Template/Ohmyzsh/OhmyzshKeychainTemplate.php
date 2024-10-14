<?php

declare(strict_types=1);

namespace Kachuru\Kute\Tools\Template\Ohmyzsh;

use Kachuru\Kute\Tools\Template;

class OhmyzshKeychainTemplate implements Template
{
    private const NAME = 'Oh-My-Zsh keychain configuration';
    private const TEMPLATE_FILE = 'ohmyzsh/keychain.zsh.tmpl';
    private const TARGET_LOCATION = '~/.oh-my-zsh/custom/keychain.zsh';
    private const TEMPLATE_DATA = [
        'identityType' => 'Specify identity type (id_rsa/id_ecdsa/...)'
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
