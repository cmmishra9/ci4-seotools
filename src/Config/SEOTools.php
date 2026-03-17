<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * SEOTools Configuration
 *
 * Copy this file to app/Config/SEOTools.php to override defaults.
 * php spark seotools:publish
 */
class SEOTools extends BaseConfig
{
    // -------------------------------------------------------------------------
    // Meta Tags
    // -------------------------------------------------------------------------

    /** @var array<string,mixed> */
    public array $meta = [
        'defaults' => [
            'title'        => false,
            'titleBefore'  => true,          // true = "Page - Site", false = "Site - Page"
            'description'  => false,
            'separator'    => ' - ',
            'keywords'     => [],
            'canonical'    => false,         // false=off, null=auto current URL, 'https://...'=fixed
            'robots'       => false,
        ],
        'webmaster_tags' => [
            'google'    => null,
            'bing'      => null,
            'alexa'     => null,
            'pinterest' => null,
            'yandex'    => null,
            'norton'    => null,
        ],
    ];

    // -------------------------------------------------------------------------
    // Open Graph
    // -------------------------------------------------------------------------

    /** @var array<string,mixed> */
    public array $opengraph = [
        'defaults' => [
            'title'       => false,
            'description' => false,
            'url'         => null,           // null = auto current URL
            'type'        => false,
            'site_name'   => false,
            'locale'      => false,
            'images'      => [],
        ],
    ];

    // -------------------------------------------------------------------------
    // Twitter / X Cards
    // -------------------------------------------------------------------------

    /** @var array<string,mixed> */
    public array $twitter = [
        'defaults' => [
            'card'        => 'summary_large_image',
            'site'        => false,
            'creator'     => false,
            'title'       => false,
            'description' => false,
            'image'       => false,
        ],
    ];

    // -------------------------------------------------------------------------
    // JSON-LD
    // -------------------------------------------------------------------------

    /** @var array<string,mixed> */
    public array $jsonld = [
        'defaults' => [
            'type'        => 'WebPage',
            'title'       => false,
            'description' => false,
            'url'         => null,           // null = auto current URL
            'images'      => [],
        ],
    ];

    // -------------------------------------------------------------------------
    // Sitemap
    // -------------------------------------------------------------------------

    /** @var array<string,mixed> */
    public array $sitemap = [
        'cache'         => true,
        'cache_duration'=> 3600,
        'gzip'          => false,
        'max_urls'      => 50000,           // per sitemap file (Google limit)
        'defaults' => [
            'changefreq' => 'weekly',
            'priority'   => '0.5',
        ],
    ];

    // -------------------------------------------------------------------------
    // Robots.txt
    // -------------------------------------------------------------------------

    /** @var array<string,mixed> */
    public array $robots = [
        'default_rules' => [
            '*' => [
                'allow'     => ['/'],
                'disallow'  => ['/admin/', '/private/'],
            ],
        ],
        /**
         * AI bot presets.
         * 'allow'     = allow crawling AND training
         * 'retrieve'  = allow crawling, block training (where supported)
         * 'disallow'  = block entirely
         */
        'ai_bots' => [
            'GPTBot'          => 'retrieve',
            'ClaudeBot'       => 'retrieve',
            'PerplexityBot'   => 'retrieve',
            'CCBot'           => 'disallow',
            'Bytespider'      => 'disallow',
            'Diffbot'         => 'retrieve',
            'anthropic-ai'    => 'retrieve',
            'Google-Extended' => 'retrieve',
        ],
    ];

    // -------------------------------------------------------------------------
    // Multi-tenant
    // -------------------------------------------------------------------------

    /**
     * Per-domain configuration overrides.
     * Key is the domain (matched against current_url()).
     *
     * @var array<string,array<string,mixed>>
     */
    public array $tenants = [];

    // -------------------------------------------------------------------------
    // Enterprise
    // -------------------------------------------------------------------------

    /** @var array<string,mixed> */
    public array $enterprise = [
        'middleware_auto_inject' => true,   // inject default SEO on every HTML response
        'schema_validation'      => true,   // validate required fields in non-production
        'debug_mode'             => false,  // output HTML comments with config trace
    ];
}
