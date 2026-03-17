<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Content;

use RcsCodes\SEOTools\Concerns\GeneratesHtml;

/**
 * ResourceHints
 *
 * Generates resource-hint <link> tags that improve Core Web Vitals
 * by telling the browser about resources it will need.
 *
 * Usage:
 *   $hints = new ResourceHints();
 *   $hints->preconnect('https://fonts.googleapis.com')
 *         ->dnsPrefetch('https://cdn.example.com')
 *         ->preload('/fonts/inter.woff2', 'font', ['crossorigin' => 'anonymous'])
 *         ->prefetch('/js/heavy-chunk.js', 'script');
 *   echo $hints->generate();
 */
class ResourceHints
{
    use GeneratesHtml;

    /** @var array<array{type:string, href:string, as:string|null, attrs:array<string,string>}> */
    protected array $hints = [];

    // ── Fluent adders ─────────────────────────────────────────────────────────

    /**
     * Preload: high-priority fetch before the browser discovers the resource.
     * Use for LCP images, critical fonts, key scripts.
     *
     * @param array<string,string> $attrs e.g. ['crossorigin'=>'anonymous','type'=>'font/woff2']
     */
    public function preload(string $href, string $as, array $attrs = []): static
    {
        return $this->addHint('preload', $href, $as, $attrs);
    }

    /**
     * Prefetch: low-priority fetch for resources needed on the *next* page.
     *
     * @param array<string,string> $attrs
     */
    public function prefetch(string $href, ?string $as = null, array $attrs = []): static
    {
        return $this->addHint('prefetch', $href, $as, $attrs);
    }

    /**
     * Preconnect: establish a connection (DNS + TCP + TLS) to an origin early.
     * Best for third-party origins used on every page (fonts, analytics, CDN).
     *
     * @param array<string,string> $attrs e.g. ['crossorigin' => ''] for CORS origins
     */
    public function preconnect(string $href, array $attrs = []): static
    {
        return $this->addHint('preconnect', $href, null, $attrs);
    }

    /**
     * DNS-prefetch: resolve DNS for an origin before it is needed.
     * Lighter than preconnect — use when you're not certain the resource will be needed.
     */
    public function dnsPrefetch(string $href): static
    {
        return $this->addHint('dns-prefetch', $href, null, []);
    }

    /**
     * Modulepreload: preload an ES module script.
     *
     * @param array<string,string> $attrs
     */
    public function modulePreload(string $href, array $attrs = []): static
    {
        return $this->addHint('modulepreload', $href, null, $attrs);
    }

    // ── Generator ─────────────────────────────────────────────────────────────

    public function generate(bool $minify = false): string
    {
        $html = [];

        foreach ($this->hints as $hint) {
            $extras = $hint['attrs'];

            if ($hint['as'] !== null) {
                $extras = \array_merge(['as' => $hint['as']], $extras);
            }
            $html[] = $this->linkTag($hint['type'], $hint['href'], $extras);
        }

        return $this->joinLines($html, $minify);
    }

    public function reset(): static
    {
        $this->hints = [];

        return $this;
    }

    // ── Internal ──────────────────────────────────────────────────────────────

    /** @param array<string,string> $attrs */
    protected function addHint(string $type, string $href, ?string $as, array $attrs): static
    {
        $this->hints[] = \compact('type', 'href', 'as', 'attrs');

        return $this;
    }
}
