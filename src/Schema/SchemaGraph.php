<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema;

/**
 * SchemaGraph
 *
 * Combines multiple Schema.org types into a single @graph block.
 * This is the recommended approach for pages that carry more than
 * one schema type (e.g. Article + BreadcrumbList + Organization).
 *
 * Usage:
 *   $graph = new SchemaGraph();
 *   $graph->add((new Article)->setHeadline('Hello World')->setAuthor('Jane'));
 *   $graph->add((new BreadcrumbList)->addItem('Home', 'https://example.com'));
 *   echo $graph->generate();
 */
class SchemaGraph
{
    /** @var array<AbstractSchema> */
    protected array $schemas = [];

    public function add(AbstractSchema $schema): static
    {
        $this->schemas[] = $schema;
        return $this;
    }

    public function remove(int $index): static
    {
        array_splice($this->schemas, $index, 1);
        return $this;
    }

    public function count(): int
    {
        return count($this->schemas);
    }

    public function isEmpty(): bool
    {
        return empty($this->schemas);
    }

    /**
     * Build the @graph data array (without wrapping <script> tag).
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph'   => array_map(
                fn(AbstractSchema $s) => $s->toEmbeddedArray(),
                $this->schemas
            ),
        ];
    }

    /**
     * Render the combined @graph as a <script type="application/ld+json"> block.
     */
    public function generate(bool $minify = false): string
    {
        foreach ($this->schemas as $schema) {
            $schema->validate();
        }

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;
        if (! $minify) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return '<script type="application/ld+json">' . json_encode($this->toArray(), $flags) . '</script>';
    }

    public function reset(): static
    {
        $this->schemas = [];
        return $this;
    }
}
