<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Technical;

use RcsCodes\SEOTools\Concerns\GeneratesHtml;

/**
 * HreflangManager
 *
 * Generates hreflang <link> tags for international SEO.
 * Handles x-default, regional variants, and self-referencing.
 *
 * Usage:
 *   $hreflang = new HreflangManager();
 *   $hreflang->addLanguage('en',    'https://example.com/en/page')
 *            ->addLanguage('en-GB', 'https://example.com/en-gb/page')
 *            ->addLanguage('fr',    'https://example.com/fr/page')
 *            ->setDefault('https://example.com/en/page');
 *   echo $hreflang->generate();
 */
class HreflangManager
{
    use GeneratesHtml;

    /** @var array<array{lang: string, url: string}> */
    protected array $languages = [];

    protected ?string $default = null;

    public function addLanguage(string $lang, string $url): static
    {
        $this->languages[] = ['lang' => $lang, 'url' => $url];
        return $this;
    }

    /**
     * Add multiple languages at once.
     *
     * @param array<string, string> $languages  ['en' => 'https://...', 'fr' => 'https://...']
     */
    public function addLanguages(array $languages): static
    {
        foreach ($languages as $lang => $url) {
            $this->addLanguage($lang, $url);
        }
        return $this;
    }

    /**
     * Set the x-default URL (shown when no language matches user's preference).
     */
    public function setDefault(string $url): static
    {
        $this->default = $url;
        return $this;
    }

    /**
     * Build all hreflang link tags.
     */
    public function generate(bool $minify = false): string
    {
        $html = [];

        foreach ($this->languages as $lang) {
            $html[] = $this->linkTag('alternate', $lang['url'], ['hreflang' => $lang['lang']]);
        }

        if ($this->default !== null) {
            $html[] = $this->linkTag('alternate', $this->default, ['hreflang' => 'x-default']);
        }

        return $this->joinLines($html, $minify);
    }

    public function reset(): static
    {
        $this->languages = [];
        $this->default   = null;
        return $this;
    }
}
