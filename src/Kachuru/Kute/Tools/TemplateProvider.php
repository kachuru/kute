<?php

declare(strict_types=1);

namespace Kachuru\Kute\Tools;

class TemplateProvider
{
    /**
     * @var Template[] $templates
     */
    private array $templates = [];

    public function addTemplate(Template $template): self
    {
        $this->templates[] = $template;
        return $this;
    }

    /** @return Template[] */
    public function getTemplates(): array
    {
        return $this->templates;
    }
}
