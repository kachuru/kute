<?php

declare(strict_types=1);

namespace Kachuru\Kute\Tools;

interface Template
{
    public function getName(): string;
    public function getFilename(): string;
    public function getTarget(): string;
    /** @return string[] */
    public function getQuestions(): array;
    /**
     * @param string[] $answers
     * @return string[]
     */
    public function processAnswers(array $answers): array;
}
