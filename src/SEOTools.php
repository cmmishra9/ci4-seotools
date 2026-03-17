<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools;

use RcsCodes\SEOTools\Concerns\MacroableTrait;
use RcsCodes\SEOTools\Contracts\SEOToolsInterface;
use RcsCodes\SEOTools\Meta\JsonLd;
use RcsCodes\SEOTools\Meta\JsonLdMulti;
use RcsCodes\SEOTools\Meta\OpenGraph;
use RcsCodes\SEOTools\Meta\SEOMeta;
use RcsCodes\SEOTools\Meta\TwitterCard;
use RcsCodes\SEOTools\Schema\SchemaGraph;
use RcsCodes\SEOTools\Technical\HreflangManager;
use RcsCodes\SEOTools\Content\ResourceHints;
use RcsCodes\SEOTools\Technical\RobotsTxt;
use RcsCodes\SEOTools\Technical\Sitemap;
use RcsCodes\SEOTools\Enterprise\EEATMarkup;

/**
 * SEOTools — the main aggregator / single entry point.
 *
 * Provides convenient access to all SEO components and proxy
 * methods for the most common operations.
 *
 * Usage (via helper functions):
 *   seo()->setTitle('My Page')->setDescription('…');
 *   seo()->opengraph()->setUrl('https://…');
 *   seo()->schema()->add(new Article)->generate();
 *   echo seo()->generate();
 *
 * Macros:
 *   SEOTools::macro('webPage', function(string $title, string $desc) { … });
 *   seo()->webPage('Home', 'Welcome');
 */
class SEOTools implements SEOToolsInterface
{
    use MacroableTrait;

    protected SEOMeta        $seoMeta;
    protected OpenGraph      $openGraph;
    protected TwitterCard    $twitterCard;
    protected JsonLd         $jsonLd;
    protected JsonLdMulti    $jsonLdMulti;
    protected SchemaGraph    $schemaGraph;
    protected HreflangManager $hreflang;
    protected ResourceHints  $resourceHints;
    protected RobotsTxt      $robotsTxt;
    protected Sitemap        $sitemap;
    protected EEATMarkup     $eeat;

    public function __construct()
    {
        $this->seoMeta       = new SEOMeta();
        $this->openGraph     = new OpenGraph();
        $this->twitterCard   = new TwitterCard();
        $this->jsonLd        = new JsonLd();
        $this->jsonLdMulti   = new JsonLdMulti();
        $this->schemaGraph   = new SchemaGraph();
        $this->hreflang      = new HreflangManager();
        $this->resourceHints = new ResourceHints();
        $this->robotsTxt     = new RobotsTxt();
        $this->sitemap       = new Sitemap();
        $this->eeat          = new EEATMarkup();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Component accessors
    // ─────────────────────────────────────────────────────────────────────────

    public function metatags(): SEOMeta
    {
        return $this->seoMeta;
    }

    public function opengraph(): OpenGraph
    {
        return $this->openGraph;
    }

    public function twitter(): TwitterCard
    {
        return $this->twitterCard;
    }

    public function jsonLd(): JsonLd
    {
        return $this->jsonLd;
    }

    public function jsonLdMulti(): JsonLdMulti
    {
        return $this->jsonLdMulti;
    }

    public function schema(): SchemaGraph
    {
        return $this->schemaGraph;
    }

    public function hreflang(): HreflangManager
    {
        return $this->hreflang;
    }

    public function resourceHints(): ResourceHints
    {
        return $this->resourceHints;
    }

    public function robots(): RobotsTxt
    {
        return $this->robotsTxt;
    }

    public function sitemap(): Sitemap
    {
        return $this->sitemap;
    }

    public function eeat(): EEATMarkup
    {
        return $this->eeat;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Convenience proxies — set one thing, propagates to all relevant components
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Set the page title on meta, OG, Twitter, and JSON-LD simultaneously.
     */
    public function setTitle(string $title): static
    {
        $this->seoMeta->setTitle($title);
        $this->openGraph->setTitle($title);
        $this->twitterCard->setTitle($title);
        $this->jsonLd->setTitle($title);
        return $this;
    }

    public function getTitle(bool $session = false): string
    {
        return $session
            ? ($this->seoMeta->getTitleSession() ?? '')
            : $this->seoMeta->getTitle();
    }

    /**
     * Set the description on all four components.
     */
    public function setDescription(string $description): static
    {
        $this->seoMeta->setDescription($description);
        $this->openGraph->setDescription($description);
        $this->twitterCard->setDescription($description);
        $this->jsonLd->setDescription($description);
        return $this;
    }

    /**
     * Set the canonical URL on SEOMeta and OG url simultaneously.
     */
    public function setCanonical(string $url): static
    {
        $this->seoMeta->setCanonical($url);
        $this->openGraph->setUrl($url);
        return $this;
    }

    /**
     * Add images to OG, Twitter Card, and JSON-LD at once.
     *
     * @param array<string>|string $urls
     */
    public function addImages(array|string $urls): static
    {
        $urls = (array) $urls;
        $this->openGraph->addImages($urls);
        if (! empty($urls)) {
            $this->twitterCard->setImage($urls[0]);
            $this->jsonLd->addImage($urls);
        }
        return $this;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Master generator
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate all configured SEO HTML tags.
     *
     * Outputs in this order:
     *   1. <title> + meta tags
     *   2. Open Graph tags
     *   3. Twitter Card tags
     *   4. Resource hints (preload / preconnect / prefetch)
     *   5. Hreflang alternate links
     *   6. JSON-LD (single or multi-block)
     *   7. Schema @graph (if entries have been added)
     */
    public function generate(bool $minify = false): string
    {
        $parts = [];

        $parts[] = $this->seoMeta->generate($minify);
        $parts[] = $this->openGraph->generate($minify);
        $parts[] = $this->twitterCard->generate($minify);
        $parts[] = $this->resourceHints->generate($minify);
        $parts[] = $this->hreflang->generate($minify);
        $parts[] = $this->jsonLd->generate($minify);

        if (! $this->schemaGraph->isEmpty()) {
            $parts[] = $this->schemaGraph->generate($minify);
        }

        $glue = $minify ? '' : "\n    ";
        return implode($glue, array_filter($parts));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reset
    // ─────────────────────────────────────────────────────────────────────────

    public function reset(): static
    {
        $this->seoMeta->reset();
        $this->openGraph->reset();
        $this->twitterCard->reset();
        $this->jsonLd->reset();
        $this->jsonLdMulti->reset();
        $this->schemaGraph->reset();
        $this->hreflang->reset();
        $this->resourceHints->reset();
        return $this;
    }
}
