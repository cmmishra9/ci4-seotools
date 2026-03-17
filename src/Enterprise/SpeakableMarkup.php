<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Enterprise;

/**
 * SpeakableMarkup
 *
 * Generates Speakable schema.org markup that signals to Google Assistant,
 * voice devices, and AI overviews which sections of a page contain the
 * most important, read-aloud-suitable content.
 *
 * Two selector strategies:
 *   1. CSS selectors  — point to DOM elements by class/ID
 *   2. XPath          — point to content blocks by xpath
 *
 * Usage:
 *   $speakable = new SpeakableMarkup();
 *   $speakable->addCssSelector('.article-headline')
 *             ->addCssSelector('.article-summary')
 *             ->setUrl(current_url());
 *   echo $speakable->generate();
 */
class SpeakableMarkup
{
    /** @var array<string> */
    protected array $cssSelectors = [];

    /** @var array<string> */
    protected array $xpaths = [];

    protected ?string $url = null;

    public function addCssSelector(string $selector): static
    {
        $this->cssSelectors[] = $selector;

        return $this;
    }

    /** @param array<string> $selectors */
    public function addCssSelectors(array $selectors): static
    {
        foreach ($selectors as $selector) {
            $this->addCssSelector($selector);
        }

        return $this;
    }

    public function addXPath(string $xpath): static
    {
        $this->xpaths[] = $xpath;

        return $this;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    // ── Output ────────────────────────────────────────────────────────────────

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'SpeakableSpecification',
        ];

        if (! empty($this->cssSelectors)) {
            $data['cssSelector'] = $this->cssSelectors;
        }

        if (! empty($this->xpaths)) {
            $data['xpath'] = $this->xpaths;
        }

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        return $data;
    }

    public function generate(bool $minify = false): string
    {
        if (empty($this->cssSelectors) && empty($this->xpaths)) {
            return '';
        }

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR | ($minify ? 0 : JSON_PRETTY_PRINT);

        return '<script type="application/ld+json">' . \json_encode($this->toArray(), $flags) . '</script>';
    }

    public function reset(): static
    {
        $this->cssSelectors = [];
        $this->xpaths       = [];
        $this->url          = null;

        return $this;
    }
}
