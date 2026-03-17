<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema;

use RcsCodes\SEOTools\Contracts\SchemaInterface;

/**
 * AbstractSchema
 *
 * Base class for all Schema.org structured-data builders.
 * Each concrete type (Article, Product, Event…) extends this.
 *
 * Key features:
 * - Fluent setter API  ($schema->setName('…')->setUrl('…'))
 * - Required-field validation with helpful messages
 * - @id support for entity linking in @graph
 * - toArray() for embedding in SchemaGraph
 * - generate() for standalone <script> output
 */
abstract class AbstractSchema implements SchemaInterface
{
    /** The Schema.org @type string, e.g. 'Article', 'Product' */
    abstract protected function schemaType(): string;

    /**
     * Fields that MUST be present for Google rich-result eligibility.
     * Override in concrete classes.
     *
     * @return array<string>
     */
    protected function requiredFields(): array
    {
        return [];
    }

    /** @var array<string,mixed> */
    protected array $data = [];

    /** @var string|null Schema.org @id (URL or blank node) */
    protected ?string $id = null;

    // ── Generic setters ───────────────────────────────────────────────────────

    /**
     * Set the @id for entity linking.
     * Use a canonical URL: e.g. 'https://example.com/#organization'
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Add / override any Schema.org property.
     */
    public function set(string $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Add a value to an array property (e.g. author, image, sameAs…).
     */
    public function append(string $key, mixed $value): static
    {
        if (! isset($this->data[$key])) {
            $this->data[$key] = [];
        }

        if (! \is_array($this->data[$key])) {
            $this->data[$key] = [$this->data[$key]];
        }
        $this->data[$key][] = $value;

        return $this;
    }

    // ── Common shared setters (used by most types) ────────────────────────────

    public function setName(string $name): static
    {
        return $this->set('name', $name);
    }

    public function setDescription(string $description): static
    {
        return $this->set('description', $description);
    }

    public function setUrl(string $url): static
    {
        return $this->set('url', $url);
    }

    /** @param string|array<string> $image */
    public function setImage(string|array $image): static
    {
        return $this->set('image', $image);
    }

    public function setSameAs(string $url): static
    {
        return $this->append('sameAs', $url);
    }

    // ── Output ────────────────────────────────────────────────────────────────

    /**
     * Assemble and return the schema as a plain array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $output = [
            '@context' => 'https://schema.org',
            '@type'    => $this->schemaType(),
        ];

        if ($this->id !== null) {
            $output['@id'] = $this->id;
        }

        foreach ($this->data as $key => $value) {
            $output[$key] = $this->resolveValue($value);
        }

        return $output;
    }

    /**
     * Return schema without @context (for embedding inside @graph or parent schemas).
     *
     * @return array<string,mixed>
     */
    public function toEmbeddedArray(): array
    {
        $arr = $this->toArray();
        unset($arr['@context']);

        return $arr;
    }

    /**
     * Render as a standalone <script type="application/ld+json"> tag.
     */
    public function generate(bool $minify = false): string
    {
        $this->validate();
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

        if (! $minify) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return '<script type="application/ld+json">' . \json_encode($this->toArray(), $flags) . '</script>';
    }

    // ── Validation ────────────────────────────────────────────────────────────

    /**
     * Validate required fields are present.
     * In CI4 development mode, throws; in production, logs a warning.
     */
    public function validate(): void
    {
        $missing = [];

        foreach ($this->requiredFields() as $field) {
            if (! isset($this->data[$field]) || $this->data[$field] === '' || $this->data[$field] === []) {
                $missing[] = $field;
            }
        }

        if (empty($missing)) {
            return;
        }

        $message = \sprintf(
            '[SEOTools] %s schema is missing required field(s): %s. '
            . 'Google may not display rich results without them.',
            $this->schemaType(),
            \implode(', ', $missing),
        );

        if (ENVIRONMENT !== 'production') {
            throw new \InvalidArgumentException($message);
        }

        log_message('warning', $message); // @phpstan-ignore-line
    }

    /**
     * Magic __call for auto-generated set*() methods.
     *
     * e.g. ->setAuthor('Jane') maps to ->set('author', 'Jane')
     *
     * @param array<mixed> $args
     */
    public function __call(string $method, array $args): mixed
    {
        if (\str_starts_with($method, 'set') && \count($args) === 1) {
            $property = \lcfirst(\substr($method, 3));

            return $this->set($property, $args[0]);
        }

        throw new \BadMethodCallException(
            \sprintf('Method %s::%s() does not exist.', static::class, $method),
        );
    }

    /**
     * Recursively resolve AbstractSchema instances to arrays.
     * Handles scalars, single schema objects, and arrays containing schema objects.
     */
    protected function resolveValue(mixed $value): mixed
    {
        if ($value instanceof self) {
            return $value->toEmbeddedArray();
        }

        if (\is_array($value)) {
            return \array_map(fn ($v) => $this->resolveValue($v), $value);
        }

        return $value;
    }


    /**
     * Reset all data (called by concrete reset() implementations).
     */
    public function reset(): static
    {
        $this->data = [];
        $this->id   = null;

        return $this;
    }

}
