<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Contracts;

interface JsonLdInterface
{
    public function setType(string $type): static;
    public function setTitle(string $title): static;
    public function setDescription(string $description): static;
    public function setUrl(string $url): static;
    /** @param array<string>|string $url */
    public function addImage(array|string $url): static;
    /** @param array<string>|string $url */
    public function setImage(array|string $url): static;
    /**  */
    public function addValue(string $key, mixed $value): static;
    /** @return array<string,mixed> */
    public function toArray(): array;
    public function reset(): static;
    public function generate(bool $minify = false): string;
}
