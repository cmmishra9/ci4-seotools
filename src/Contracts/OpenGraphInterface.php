<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Contracts;

interface OpenGraphInterface
{
    /** @param array<string,mixed>|string $value */
    public function addProperty(string $key, array|string $value): static;
    public function setTitle(string $title): static;
    public function setDescription(string $description): static;
    public function setUrl(string $url): static;
    public function setType(string $type): static;
    public function setSiteName(string $name): static;
    /**
     * @param string|array<string,string> $url
     * @param array<string,string> $attrs
     */
    public function addImage(array|string $url, array $attrs = []): static;
    /** @param array<mixed> $urls */
    public function addImages(array $urls): static;
    /** @param array<string,string> $attrs */
    public function addVideo(string $url, array $attrs = []): static;
    /** @param array<string,string> $attrs */
    public function addAudio(string $url, array $attrs = []): static;
    public function reset(): static;
    public function generate(bool $minify = false): string;
}
