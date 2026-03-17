<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Contracts;

interface SEOToolsInterface
{
    public function metatags(): MetaTagsInterface;
    public function opengraph(): OpenGraphInterface;
    public function twitter(): TwitterCardInterface;
    public function jsonLd(): JsonLdInterface;
    public function setTitle(string $title): static;
    public function getTitle(bool $session = false): string;
    public function setDescription(string $description): static;
    public function setCanonical(string $url): static;
    /** @param array<string>|string $urls */
    public function addImages(array|string $urls): static;
    public function generate(bool $minify = false): string;
    public function reset(): static;
}
