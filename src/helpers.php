<?php

declare(strict_types=1);

use RcsCodes\SEOTools\Content\ResourceHints;
use RcsCodes\SEOTools\Enterprise\EEATMarkup;
use RcsCodes\SEOTools\Meta\JsonLd;
use RcsCodes\SEOTools\Meta\JsonLdMulti;
use RcsCodes\SEOTools\Meta\OpenGraph;
use RcsCodes\SEOTools\Meta\SEOMeta;
use RcsCodes\SEOTools\Meta\TwitterCard;
use RcsCodes\SEOTools\Schema\SchemaGraph;
use RcsCodes\SEOTools\SEOTools;
use RcsCodes\SEOTools\Technical\HreflangManager;
use RcsCodes\SEOTools\Technical\RobotsTxt;
use RcsCodes\SEOTools\Technical\Sitemap;

/**
 * SEOTools Global Helpers
 *
 * Provides globally accessible factory/singleton functions equivalent
 * to Laravel facades. Auto-loaded by Composer (see autoload.files).
 *
 * One SEOTools instance is shared per request via a static variable.
 * Call seo_reset() at the start of each request if reusing across tests.
 */

// ─────────────────────────────────────────────────────────────────────────────
// Singleton container
// ─────────────────────────────────────────────────────────────────────────────

if (! \function_exists('_seotools_instance')) {
    function _seotools_instance(): SEOTools
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new SEOTools();
        }

        return $instance;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Primary entry point
// ─────────────────────────────────────────────────────────────────────────────

if (! \function_exists('seo')) {
    /**
     * Return the shared SEOTools instance (all-in-one access).
     *
     * @example seo()->setTitle('Home')->setDescription('…');
     * @example echo seo()->generate();
     */
    function seo(): SEOTools
    {
        return _seotools_instance();
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Component shortcuts
// ─────────────────────────────────────────────────────────────────────────────

if (! \function_exists('seo_meta')) {
    /** @example seo_meta()->setTitle('Home'); */
    function seo_meta(): SEOMeta
    {
        return _seotools_instance()->metatags();
    }
}

if (! \function_exists('seo_og')) {
    /** @example seo_og()->setUrl('https://…')->addProperty('type', 'article'); */
    function seo_og(): OpenGraph
    {
        return _seotools_instance()->opengraph();
    }
}

if (! \function_exists('seo_twitter')) {
    /** @example seo_twitter()->setSite('@handle')->setImage('https://…/cover.jpg'); */
    function seo_twitter(): TwitterCard
    {
        return _seotools_instance()->twitter();
    }
}

if (! \function_exists('seo_jsonld')) {
    /** @example seo_jsonld()->setType('Article')->addImage('https://…/img.jpg'); */
    function seo_jsonld(): JsonLd
    {
        return _seotools_instance()->jsonLd();
    }
}

if (! \function_exists('seo_jsonld_multi')) {
    /** @example seo_jsonld_multi()->newJsonLd()->setType('WebPage'); */
    function seo_jsonld_multi(): JsonLdMulti
    {
        return _seotools_instance()->jsonLdMulti();
    }
}

if (! \function_exists('seo_schema')) {
    /**
     * Access the @graph schema builder.
     *
     * @example seo_schema()->add(new Article)->add(new BreadcrumbList)->generate();
     */
    function seo_schema(): SchemaGraph
    {
        return _seotools_instance()->schema();
    }
}

if (! \function_exists('seo_sitemap')) {
    /** @example seo_sitemap()->addUrl('https://…', 'daily', '1.0')->toResponse(); */
    function seo_sitemap(): Sitemap
    {
        return _seotools_instance()->sitemap();
    }
}

if (! \function_exists('seo_robots')) {
    /** @example seo_robots()->applyAiBotPresets()->addSitemap('https://…/sitemap.xml'); */
    function seo_robots(): RobotsTxt
    {
        return _seotools_instance()->robots();
    }
}

if (! \function_exists('seo_hreflang')) {
    /** @example seo_hreflang()->addLanguage('en', 'https://…')->setDefault('https://…'); */
    function seo_hreflang(): HreflangManager
    {
        return _seotools_instance()->hreflang();
    }
}

if (! \function_exists('seo_hints')) {
    /** @example seo_hints()->preconnect('https://fonts.googleapis.com')->preload('/font.woff2', 'font'); */
    function seo_hints(): ResourceHints
    {
        return _seotools_instance()->resourceHints();
    }
}

if (! \function_exists('seo_eeat')) {
    /** @example seo_eeat()->setAuthor('Jane Smith')->addAuthorSameAs('https://linkedin.com/in/jane'); */
    function seo_eeat(): EEATMarkup
    {
        return _seotools_instance()->eeat();
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Utility
// ─────────────────────────────────────────────────────────────────────────────

if (! \function_exists('seo_reset')) {
    /**
     * Reset the shared instance (useful in tests or multi-request CLi scripts).
     */
    function seo_reset(): void
    {
        _seotools_instance()->reset();
    }
}

if (! \function_exists('seo_generate')) {
    /**
     * Generate all SEO tags and return the HTML string.
     *
     * @example <?= seo_generate() ?> inside your <head>
     */
    function seo_generate(bool $minify = false): string
    {
        return _seotools_instance()->generate($minify);
    }
}
