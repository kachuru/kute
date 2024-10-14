<?php

declare(strict_types=1);

namespace Kachuru\Kute\Tools;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TemplateRenderer
{
    private Environment $twig;

    public function __construct()
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . DIRECTORY_SEPARATOR . 'templates'), []);
    }

    public function render($template, $vars = []): string
    {
        return $this->twig->render($template, $vars);
    }
}
