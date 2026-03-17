<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Meta;

use RcsCodes\SEOTools\Concerns\GeneratesHtml;
use RcsCodes\SEOTools\Concerns\HasDefaults;
use RcsCodes\SEOTools\Contracts\MetaTagsInterface;

/**
 * SEOMeta
 *
 * Manages and renders all standard HTML meta tags:
 * <title>, description, keywords, robots, canonical,
 * pagination (prev/next), hreflang alternates, and
 * webmaster verification tags.
 */
class SEOMeta implements MetaTagsInterface
{
    use HasDefaults;
    use GeneratesHtml;

    protected ?string $title            = null;
    protected ?string $titleDefault     = null;
    protected string  $titleSeparator   = ' - ';
    protected bool    $titleBefore      = true;
    protected ?string $description      = null;
    /** @var array<string> */
    protected array   $keywords         = [];
    /**
     * @var array<array{attribute:string,name:string,content:string}>
     */
    protected array   $metas            = [];
    protected ?string $canonical        = null;
    protected ?string $prev             = null;
    protected ?string $next             = null;
    protected ?string $robots           = null;
    /**
     * @var array<array{lang:string,url:string}>
     */
    protected array   $alternateLanguages = [];

    /** @var array<string,array{name:string,attr:string}> */
    protected array $webmasterTagMap = [
        'google'    => ['name' => 'google-site-verification',        'attr' => 'name'],
        'bing'      => ['name' => 'msvalidate.01',                   'attr' => 'name'],
        'alexa'     => ['name' => 'alexaVerifyID',                   'attr' => 'name'],
        'pinterest' => ['name' => 'p:domain_verify',                 'attr' => 'name'],
        'yandex'    => ['name' => 'yandex-verification',             'attr' => 'name'],
        'norton'    => ['name' => 'norton-safeweb-site-verification', 'attr' => 'name'],
    ];

    public function __construct()
    {
        $this->bootConfig();
        $this->applyDefaults();
    }

    // -------------------------------------------------------------------------
    // Setters
    // -------------------------------------------------------------------------

    public function setTitle(string $title): static
    {
        $this->title = \strip_tags($title);

        return $this;
    }

    public function setTitleDefault(string $default): static
    {
        $this->titleDefault = \strip_tags($default);

        return $this;
    }

    public function setTitleSeparator(string $separator): static
    {
        $this->titleSeparator = $separator;

        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->description = \strip_tags($description);

        return $this;
    }

    public function setKeywords(array|string $keywords): static
    {
        if (\is_string($keywords)) {
            $keywords = \explode(',', $keywords);
        }
        $this->keywords = \array_map('trim', $keywords);

        return $this;
    }

    public function addKeyword(array|string $keyword): static
    {
        if (\is_array($keyword)) {
            $this->keywords = \array_merge($this->keywords, \array_map('trim', $keyword));
        } else {
            $this->keywords[] = \trim($keyword);
        }

        return $this;
    }

    public function setRobots(string $robots): static
    {
        $this->robots = $robots;

        return $this;
    }

    public function setCanonical(string $url): static
    {
        $this->canonical = $url;

        return $this;
    }

    public function setPrev(string $url): static
    {
        $this->prev = $url;

        return $this;
    }

    public function setNext(string $url): static
    {
        $this->next = $url;

        return $this;
    }

    public function addMeta(array|string $meta, ?string $value = null, string $name = 'name'): static
    {
        if (\is_array($meta)) {
            foreach ($meta as $key => $val) {
                $this->metas[] = ['attribute' => $name, 'name' => $key, 'content' => (string) $val];
            }
        } else {
            $this->metas[] = ['attribute' => $name, 'name' => $meta, 'content' => (string) $value];
        }

        return $this;
    }

    public function removeMeta(string $key): static
    {
        $this->metas = \array_values(
            \array_filter($this->metas, fn ($m) => $m['name'] !== $key),
        );

        return $this;
    }

    public function setAlternateLanguage(string $lang, string $url): static
    {
        $this->alternateLanguages = [['lang' => $lang, 'url' => $url]];

        return $this;
    }

    /** @param array<array{lang:string,url:string}> $languages */
    public function setAlternateLanguages(array $languages): static
    {
        $this->alternateLanguages = $languages;

        return $this;
    }

    public function addAlternateLanguage(string $lang, string $url): static
    {
        $this->alternateLanguages[] = ['lang' => $lang, 'url' => $url];

        return $this;
    }

    /** @param array<array{lang:string,url:string}> $languages */
    public function addAlternateLanguages(array $languages): static
    {
        $this->alternateLanguages = \array_merge($this->alternateLanguages, $languages);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public function getTitle(): string
    {
        return $this->buildTitle();
    }

    public function getTitleSession(): ?string
    {
        return $this->title;
    }

    public function getTitleSeparator(): string
    {
        return $this->titleSeparator;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /** @return array<string> */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function getCanonical(): ?string
    {
        if ($this->canonical === '__auto__') {
            return current_url();
        }

        return $this->canonical;
    }

    public function getPrev(): ?string
    {
        return $this->prev;
    }

    public function getNext(): ?string
    {
        return $this->next;
    }

    public function getRobots(): ?string
    {
        return $this->robots;
    }

    // -------------------------------------------------------------------------
    // Generate
    // -------------------------------------------------------------------------

    public function generate(bool $minify = false): string
    {
        $html = [];

        $builtTitle = $this->buildTitle();

        if ($builtTitle !== '') {
            $html[] = '<title>' . esc($builtTitle) . '</title>';
        }

        if ($this->description !== null && $this->description !== '') {
            $html[] = '<meta name="description" itemprop="description" content="' . esc($this->description) . '">';
        }

        if (! empty($this->keywords)) {
            $html[] = '<meta name="keywords" content="' . esc(\implode(', ', $this->keywords)) . '">';
        }

        if ($this->robots !== null && $this->robots !== '') {
            $html[] = $this->metaNameTag('robots', $this->robots);
        }

        if ($this->canonical !== null && $this->canonical !== '') {
            $resolved = $this->canonical === '__auto__' ? current_url() : $this->canonical;
            $html[] = $this->linkTag('canonical', $resolved);
        }

        if ($this->prev) {
            $html[] = $this->linkTag('prev', $this->prev);
        }

        if ($this->next) {
            $html[] = $this->linkTag('next', $this->next);
        }

        foreach ($this->alternateLanguages as $alt) {
            $html[] = $this->linkTag('alternate', $alt['url'], ['hreflang' => $alt['lang']]);
        }

        foreach ($this->metas as $meta) {
            $html[] = $this->metaTag($meta['attribute'], $meta['name'], $meta['content']);
        }

        // Webmaster verification tags
        foreach ($this->config->meta['webmaster_tags'] ?? [] as $service => $value) {
            if ($value !== null && isset($this->webmasterTagMap[$service])) {
                $tag    = $this->webmasterTagMap[$service];
                $html[] = $this->metaTag($tag['attr'], $tag['name'], (string) $value);
            }
        }

        return $this->joinLines($html, $minify);
    }

    // -------------------------------------------------------------------------
    // Reset
    // -------------------------------------------------------------------------

    public function reset(): static
    {
        $this->title              = null;
        $this->titleDefault       = null;
        $this->titleSeparator     = ' - ';
        $this->titleBefore        = true;
        $this->description        = null;
        $this->keywords           = [];
        $this->metas              = [];
        $this->canonical          = null;
        $this->prev               = null;
        $this->next               = null;
        $this->robots             = null;
        $this->alternateLanguages = [];
        $this->applyDefaults();

        return $this;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    protected function applyDefaults(): void
    {
        $d = $this->config->meta['defaults'] ?? [];

        if (! empty($d['title'])) {
            $this->titleDefault = (string) $d['title'];
        }

        if (! empty($d['separator'])) {
            $this->titleSeparator = (string) $d['separator'];
        }

        if (isset($d['titleBefore'])) {
            $this->titleBefore = (bool) $d['titleBefore'];
        }

        if (! empty($d['description'])) {
            $this->description = (string) $d['description'];
        }

        if (! empty($d['keywords'])) {
            $this->keywords = (array) $d['keywords'];
        }

        if (! empty($d['robots'])) {
            $this->robots = (string) $d['robots'];
        }

        if (\array_key_exists('canonical', $d) && $d['canonical'] !== false) {
            // null means 'auto-detect at render time'; false disables canonical entirely
            $this->canonical = $d['canonical'] === null ? '__auto__' : (string) $d['canonical'];
        }
    }

    protected function buildTitle(): string
    {
        $page = $this->title;
        $site = $this->titleDefault;

        if ($page !== null && $site !== null) {
            return $this->titleBefore
                ? $page . $this->titleSeparator . $site
                : $site . $this->titleSeparator . $page;
        }

        return $page ?? $site ?? '';
    }
}
