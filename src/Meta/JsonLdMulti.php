<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Meta;

/**
 * JsonLdMulti
 *
 * Manages multiple JSON-LD groups on the same page.
 * Delegates all setter calls to the currently active JsonLd instance.
 */
class JsonLdMulti
{
    /** @var array<JsonLd> */
    protected array $groups  = [];
    protected int   $current = 0;

    public function __construct()
    {
        $this->groups[] = new JsonLd();
    }

    public function newJsonLd(): static
    {
        $this->groups[] = new JsonLd();
        $this->current  = \count($this->groups) - 1;

        return $this;
    }

    public function select(int $index): static
    {
        if (isset($this->groups[$index])) {
            $this->current = $index;
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        foreach ($this->groups as $group) {
            if (\count($group->toArray()) > 2) {
                return false;
            }
        }

        return true;
    }

    // ── Proxied setters ───────────────────────────────────────────────────────

    public function setType(string $type): static
    {
        $this->active()->setType($type);

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->active()->setTitle($title);

        return $this;
    }

    public function setSite(string $site): static
    {
        $this->active()->setSite($site);

        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->active()->setDescription($description);

        return $this;
    }

    public function setUrl(string $url): static
    {
        $this->active()->setUrl($url);

        return $this;
    }

    /** @param array<string>|string $url */
    public function addImage(array|string $url): static
    {
        $this->active()->addImage($url);

        return $this;
    }

    /** @param array<string>|string $url */
    public function setImage(array|string $url): static
    {
        $this->active()->setImage($url);

        return $this;
    }

    public function addValue(string $key, mixed $value): static
    {
        $this->active()->addValue($key, $value);

        return $this;
    }

    // ── Generator ─────────────────────────────────────────────────────────────

    public function generate(bool $minify = false): string
    {
        $parts = \array_map(fn (JsonLd $g) => $g->generate($minify), $this->groups);

        return \implode($minify ? '' : "\n    ", $parts);
    }

    public function reset(): static
    {
        $this->groups  = [new JsonLd()];
        $this->current = 0;

        return $this;
    }

    protected function active(): JsonLd
    {
        return $this->groups[$this->current];
    }
}
