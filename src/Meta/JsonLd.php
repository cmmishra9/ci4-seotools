<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Meta;

use RcsCodes\SEOTools\Concerns\HasDefaults;
use RcsCodes\SEOTools\Contracts\JsonLdInterface;

/**
 * JsonLd
 *
 * Builds and renders a single JSON-LD <script type="application/ld+json"> block.
 *
 * @see https://json-ld.org/
 * @see https://schema.org/
 */
class JsonLd implements JsonLdInterface
{
    use HasDefaults;

    protected string  $type        = 'WebPage';
    protected ?string $title       = null;
    protected ?string $description = null;
    protected ?string $url         = null;
    /** @var array<string> */
    protected array   $images = [];
    /** @var array<string,mixed> */
    protected array   $values = [];

    public function __construct()
    {
        $this->bootConfig();
        $this->applyDefaults();
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function setSite(string $site): static
    {
        return $this->setTitle($site);
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function addImage(array|string $url): static
    {
        if (is_array($url)) {
            $this->images = array_merge($this->images, $url);
        } else {
            $this->images[] = $url;
        }
        return $this;
    }

    public function setImage(array|string $url): static
    {
        $this->images = [];
        return $this->addImage($url);
    }

    public function addValue(string $key, mixed $value): static
    {
        $this->values[$key] = $value;
        return $this;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => $this->type,
        ];

        if ($this->title !== null)       $data['name']        = $this->title;
        if ($this->description !== null) $data['description'] = $this->description;
        if ($this->url !== null)         $data['url']         = $this->url;

        if (! empty($this->images)) {
            $data['image'] = count($this->images) === 1 ? $this->images[0] : $this->images;
        }

        foreach ($this->values as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    public function isEmpty(): bool
    {
        return count($this->toArray()) <= 2;
    }

    public function generate(bool $minify = false): string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;
        if (! $minify) {
            $flags |= JSON_PRETTY_PRINT;
        }
        return '<script type="application/ld+json">' . json_encode($this->toArray(), $flags) . '</script>';
    }

    public function reset(): static
    {
        $this->type        = 'WebPage';
        $this->title       = null;
        $this->description = null;
        $this->url         = null;
        $this->images      = [];
        $this->values      = [];
        $this->applyDefaults();
        return $this;
    }

    protected function applyDefaults(): void
    {
        $d = $this->config->jsonld['defaults'] ?? [];

        if (! empty($d['type']))        $this->type        = (string) $d['type'];
        if (! empty($d['title']))       $this->title       = (string) $d['title'];
        if (! empty($d['description'])) $this->description = (string) $d['description'];
        if (! empty($d['images']))      $this->images      = (array)  $d['images'];

        if (array_key_exists('url', $d) && $d['url'] !== false) {
            $this->url = $d['url'] === null ? current_url() : (string) $d['url'];
        }
    }
}
