<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Contracts;

interface TwitterCardInterface
{
    /** @param array<mixed>|string $value */
    public function addValue(string $key, array|string $value): static;
    public function setType(string $type): static;
    public function setTitle(string $title): static;
    public function setSite(string $site): static;
    public function setDescription(string $description): static;
    public function setUrl(string $url): static;
    public function setImage(string $url): static;
    public function reset(): static;
    public function generate(bool $minify = false): string;
}
