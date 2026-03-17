<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * SEOTools Configuration
 *
 * Copy this file to app/Config/SEOTools.php (via `php spark seotools:publish`)
 * and customise the defaults for your application.
 */
class SEOTools extends BaseConfig
{
    // ─────────────────────────────────────────────────────────────────────────
    // Meta tags
    // ─────────────────────────────────────────────────────────────────────────

    public array $meta = [
        'defaults' => [
            /**
             * Site name appended to every page title.
             * Set to null or '' to disable.
             */
            'title'       => 'My Site',

            /**
             * true  → "Page Title - Site Name"
             * false → "Site Name - Page Title"
             */
            'titleBefore' => true,

            /** Separator between page title and site name. */
            'separator'   => ' - ',

            /** Default description when none is set per page. false = disabled. */
            'description' => false,

            /** Default keywords. */
            'keywords'    => [],

            /**
             * Default canonical URL.
             * null = auto-detect from current_url() on each request.
             */
            'canonical'   => null,

            /** Default robots directive. */
            'robots'      => 'index, follow',
        ],

        /**
         * Webmaster verification meta tags.
         * Set the value for each service you use; leave null to skip.
         */
        'webmaster_tags' => [
            'google'       => null,   // Google Search Console
            'bing'         => null,   // Bing Webmaster Tools
            'yandex'       => null,   // Yandex.Webmaster
            'pinterest'    => null,   // Pinterest
            'norton'       => null,   // Norton Safe Web
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Open Graph
    // ─────────────────────────────────────────────────────────────────────────

    public array $opengraph = [
        'defaults' => [
            /** The name that appears next to the Facebook share button. */
            'site_name' => 'My Site',

            /** Default OG type for pages that don't set one. */
            'type'      => 'website',

            /** Default OG URL. null = auto-detect from current_url(). */
            'url'       => null,

            /** Default OG locale. */
            'locale'    => 'en_US',
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Twitter / X Cards
    // ─────────────────────────────────────────────────────────────────────────

    public array $twitter = [
        'defaults' => [
            /**
             * Default card type.
             * summary | summary_large_image | app | player
             */
            'card'    => 'summary_large_image',

            /** Your @handle — applied to every card. */
            'site'    => null,
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // JSON-LD
    // ─────────────────────────────────────────────────────────────────────────

    public array $jsonld = [
        'defaults' => [
            'type'  => 'WebPage',
            'title' => null,
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Sitemap
    // ─────────────────────────────────────────────────────────────────────────

    public array $sitemap = [
        'defaults' => [
            /** Default changefreq for URLs that don't specify one. */
            'changefreq' => 'weekly',

            /** Default priority (0.0–1.0). */
            'priority'   => '0.5',
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Robots.txt
    // ─────────────────────────────────────────────────────────────────────────

    public array $robots = [
        /**
         * Default rules applied to every generated robots.txt.
         * Format: ['User-Agent' => ['allow' => [...], 'disallow' => [...]]]
         */
        'default_rules' => [
            '*' => [
                'allow'    => ['/'],
                'disallow' => ['/admin/', '/private/'],
            ],
        ],

        /**
         * AI bot governance.
         * For each bot specify: 'allow' | 'retrieve' | 'disallow'
         *
         *   allow    → Bot can crawl AND use for training.
         *   retrieve → Bot can crawl for search/answers only (training blocked).
         *   disallow → Bot is blocked entirely.
         */
        'ai_bots' => [
            'GPTBot'          => 'retrieve',
            'ClaudeBot'       => 'retrieve',
            'anthropic-ai'    => 'retrieve',
            'PerplexityBot'   => 'retrieve',
            'Google-Extended' => 'retrieve',
            'Applebot-Extended' => 'retrieve',
            'FacebookBot'     => 'retrieve',
            'CCBot'           => 'disallow',
            'Bytespider'      => 'disallow',
            'Diffbot'         => 'disallow',
            'omgili'          => 'disallow',
            'omgilibot'       => 'disallow',
        ],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Multi-tenant overrides
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Per-domain config overrides for multi-tenant / white-label applications.
     * Keys are exact domain names or wildcard patterns (e.g. '*.brand-b.com').
     * Values are arrays of config section overrides.
     *
     * Example:
     *   'brand-a.example.com' => [
     *       'meta'      => ['defaults' => ['title' => 'Brand A']],
     *       'opengraph' => ['defaults' => ['site_name' => 'Brand A']],
     *   ],
     */
    public array $tenants = [];

    // ─────────────────────────────────────────────────────────────────────────
    // Enterprise features
    // ─────────────────────────────────────────────────────────────────────────

    public array $enterprise = [
        /**
         * When true, SEOMiddleware auto-injects a canonical tag into HTML
         * responses that don't already have one.
         */
        'middleware_auto_inject' => true,

        /**
         * When true, Schema.org types throw InvalidArgumentException in
         * development if required fields are missing.
         * In production they silently log a warning instead.
         */
        'schema_validation' => true,

        /** Enable extra SEO debug logging. */
        'debug_mode' => false,
    ];
}
