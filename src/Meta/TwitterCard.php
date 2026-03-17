<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Meta;

use RcsCodes\SEOTools\Concerns\GeneratesHtml;
use RcsCodes\SEOTools\Concerns\HasDefaults;
use RcsCodes\SEOTools\Contracts\TwitterCardInterface;

/**
 * TwitterCard
 *
 * Generates twitter:* meta tags for Twitter/X Card support.
 *
 * @see https://developer.twitter.com/en/docs/twitter-for-websites/cards
 */
class TwitterCard implements TwitterCardInterface
{
    use HasDefaults;
    use GeneratesHtml;

    /** @var array<string,mixed> */
    protected array $values = [];

    public function __construct()
    {
        $this->bootConfig();
        $this->applyDefaults();
    }

    public function addValue(string $key, array|string $value): static
    {
        // Guard: strip leading 'twitter:' prefix — added automatically in generate()
        if (str_starts_with($key, 'twitter:')) {
            $key = substr($key, 8);
        }
        $this->values[$key] = $value;
        return $this;
    }

    public function setType(string $type): static
    {
        return $this->addValue('card', $type);
    }

    public function setTitle(string $title): static
    {
        return $this->addValue('title', $title);
    }

    public function setSite(string $site): static
    {
        return $this->addValue('site', $site);
    }

    public function setCreator(string $creator): static
    {
        return $this->addValue('creator', $creator);
    }

    public function setDescription(string $description): static
    {
        return $this->addValue('description', $description);
    }

    public function setUrl(string $url): static
    {
        return $this->addValue('url', $url);
    }

    public function setImage(string $url): static
    {
        return $this->addValue('image', $url);
    }

    public function setImageAlt(string $alt): static
    {
        return $this->addValue('image:alt', $alt);
    }

    public function generate(bool $minify = false): string
    {
        $html = [];

        foreach ($this->values as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $html[] = $this->metaNameTag('twitter:' . $key, (string) $val);
                }
            } else {
                $html[] = $this->metaNameTag('twitter:' . $key, (string) $value);
            }
        }

        return $this->joinLines($html, $minify);
    }

    public function reset(): static
    {
        $this->values = [];
        return $this;
    }

    protected function applyDefaults(): void
    {
        foreach ($this->config->twitter['defaults'] ?? [] as $key => $value) {
            if ($value !== false && $value !== null) {
                $this->values[$key] = $value;
            }
        }
    }
}
