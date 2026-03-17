<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Contracts;

/**
 * SchemaInterface
 *
 * Contract for all Schema.org structured-data builder classes.
 */
interface SchemaInterface
{
    public function setId(string $id): static;

    public function set(string $key, mixed $value): static;

    public function append(string $key, mixed $value): static;

    public function setName(string $name): static;

    public function setDescription(string $description): static;

    public function setUrl(string $url): static;

    /** @param string|array<string> $image */
    public function setImage(string|array $image): static;

    public function setSameAs(string $url): static;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array;

    /**
     * Return schema without @context (for embedding in @graph or parent schemas).
     *
     * @return array<string,mixed>
     */
    public function toEmbeddedArray(): array;

    public function generate(bool $minify = false): string;

    public function validate(): void;

    public function reset(): static;
}
