<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Contracts;

interface MetaTagsInterface
{
    public function setTitle(string $title): static;
    public function setTitleDefault(string $default): static;
    public function setTitleSeparator(string $separator): static;
    public function setDescription(string $description): static;
    /** @param array<string>|string $keywords */
    public function setKeywords(array|string $keywords): static;
    /** @param array<string>|string $keyword */
    public function addKeyword(array|string $keyword): static;
    public function setRobots(string $robots): static;
    public function setCanonical(string $url): static;
    public function setPrev(string $url): static;
    public function setNext(string $url): static;
    /** @param array<string,string>|string $meta */
    public function addMeta(array|string $meta, ?string $value = null, string $name = 'name'): static;
    public function removeMeta(string $key): static;
    public function addAlternateLanguage(string $lang, string $url): static;
    /** @param array<array{lang:string,url:string}> $languages */
    public function addAlternateLanguages(array $languages): static;
    public function getTitle(): string;
    public function getTitleSession(): ?string;
    public function getTitleSeparator(): string;
    public function getDescription(): ?string;
    /** @return array<string> */
    public function getKeywords(): array;
    public function getCanonical(): ?string;
    public function getRobots(): ?string;
    public function reset(): static;
    public function generate(bool $minify = false): string;
}
