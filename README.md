# ci4-seotools

Enterprise-grade SEO Tools for **CodeIgniter 4** — a complete port and extension of the battle-tested ecosystem, rebuilt from the ground up for CI4 with schema validation, AI-bot governance, multi-tenancy, and ≥95% test coverage.

[![CI](https://github.com/cmmishra9/ci4-seotools/actions/workflows/ci.yml/badge.svg)](https://github.com/cmmishra9/ci4-seotools/actions)
[![Latest Version](https://img.shields.io/packagist/v/cmmishra9/ci4-seotools.svg)](https://packagist.org/packages/cmmishra9/ci4-seotools)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://packagist.org/packages/cmmishra9/ci4-seotools)
[![License](https://img.shields.io/packagist/l/cmmishra9/ci4-seotools.svg)](LICENSE.md)

---

## Table of contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Components](#components)
  - [SEOMeta](#seometa)
  - [OpenGraph](#opengraph)
  - [TwitterCard](#twittercard)
  - [JsonLd / JsonLdMulti](#jsonld--jsonldmulti)
  - [Schema types](#schema-types)
  - [SchemaGraph](#schemagraph)
  - [Sitemap](#sitemap)
  - [SitemapIndex](#sitemapindex)
  - [RobotsTxt](#robotstxt)
  - [HreflangManager](#hreflangmanager)
  - [RedirectHelper](#redirecthelper)
  - [ResourceHints](#resourcehints)
  - [AiBotManager](#aibotmanager)
  - [EEATMarkup](#eeatmarkup)
  - [SEOMiddleware](#seomiddleware)
  - [MultiTenantManager](#multitenantmanager)
- [Global helpers](#global-helpers)
- [Macros](#macros)
- [Configuration reference](#configuration-reference)
- [Testing](#testing)
- [Changelog](#changelog)
- [License](#license)

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | `^8.1` |
| CodeIgniter | `^4.4` |

---

## Installation

```bash
composer require cmmishra9/ci4-seotools
```

Publish the config file and view partials to your app:

```bash
php spark seotools:publish
```

This copies `app/Config/SEOTools.php` and `app/Views/seotools/` into your project. The config is fully annotated — read it before you deploy.

---

## Quick start

In your `<head>` layout (e.g. `app/Views/layouts/default.php`):

```php
<?= seo_generate() ?>
```

In a controller:

```php
seo()->setTitle('Home')
     ->setDescription('Welcome to Acme Corp.')
     ->setCanonical('https://acme.com/');

seo_og()->setSiteName('Acme Corp')->setType('website');
seo_twitter()->setSite('@acmecorp')->setType('summary_large_image');
```

---

## Components

### SEOMeta

Generates `<title>`, meta description, keywords, robots, canonical, pagination links, hreflang alternates, and webmaster verification tags.

```php
use RcsCodes\SEOTools\Meta\SEOMeta;

$meta = new SEOMeta();
$meta->setTitle('Article Title')
     ->setTitleDefault('My Site')       // produces: Article Title | My Site
     ->setTitleSeparator(' | ')
     ->setDescription('A concise description under 160 chars.')
     ->setKeywords(['php', 'codeigniter', 'seo'])
     ->setRobots('index, follow')
     ->setCanonical('https://example.com/article')
     ->setPrev('https://example.com/page/1')
     ->setNext('https://example.com/page/3')
     ->addAlternateLanguage('fr', 'https://fr.example.com/article')
     ->addMeta('theme-color', '#ff5500');

echo $meta->generate();
```

**Config-driven defaults** — set `meta.defaults` in `app/Config/SEOTools.php` to pre-populate title, description, robots, and canonical across every request.

---

### OpenGraph

Generates all `og:*` meta property tags with full namespace support.

```php
seo_og()
    ->setTitle('Article Title')
    ->setDescription('Open Graph description.')
    ->setUrl('https://example.com/article')
    ->setType('article')
    ->setSiteName('My Site')
    ->setLocale('en_US')
    ->addImage('https://example.com/og.jpg', [
        'width'  => '1200',
        'height' => '630',
        'alt'    => 'Hero image',
    ])
    ->setArticle([
        'published_time' => '2024-06-01T12:00:00Z',
        'author'         => 'https://example.com/author/jane',
        'section'        => 'Technology',
        'tag'            => ['PHP', 'SEO'],
    ]);
```

Supported namespaces: `article`, `book`, `profile`, `music`, `video`, `place`.

---

### TwitterCard

```php
seo_twitter()
    ->setType('summary_large_image')
    ->setSite('@mysite')
    ->setCreator('@author')
    ->setTitle('Article Title')
    ->setDescription('Card description.')
    ->setImage('https://example.com/card.jpg')
    ->setImageAlt('Card image description');
```

---

### JsonLd / JsonLdMulti

```php
// Single block
seo_jsonld()
    ->setType('Article')
    ->setTitle('Article Title')
    ->setDescription('Article description.')
    ->setUrl('https://example.com/article')
    ->addImage('https://example.com/img.jpg')
    ->addValue('author', ['@type' => 'Person', 'name' => 'Jane Doe']);

// Multiple blocks on one page
seo_jsonld_multi()
    ->setType('WebPage')->setTitle('Home');

seo_jsonld_multi()
    ->newJsonLd()
    ->setType('Organization')
    ->addValue('name', 'Acme Corp');
```

---

### Schema types

Full Schema.org rich result types with required-field validation (throws in development, logs in production):

| Class | Required fields | Google rich result |
|---|---|---|
| `Article` | headline, author, datePublished | ✓ |
| `NewsArticle` | headline, image, datePublished, author | ✓ News |
| `Product` + `Offer` | name, image, description + price, priceCurrency | ✓ |
| `BreadcrumbList` | itemListElement (via addItem) | ✓ |
| `FAQPage` | mainEntity (via addQuestion) | ✓ |
| `HowTo` | name, step (via addStep) | ✓ |
| `Event` | name, startDate, location | ✓ |
| `Course` | name, description, provider | ✓ |
| `Recipe` | name, image, author, datePublished, description | ✓ |
| `Review` | itemReviewed, reviewRating, author | ✓ |
| `VideoObject` | name, description, thumbnailUrl, uploadDate | ✓ |
| `JobPosting` | title, description, hiringOrganization, datePosted, jobLocation | ✓ |
| `SoftwareApplication` | name, operatingSystem, applicationCategory | ✓ |
| `Organization` | name | — |
| `LocalBusiness` | name, address | ✓ Local |

```php
use RcsCodes\SEOTools\Schema\Types\Article;
use RcsCodes\SEOTools\Schema\Types\BreadcrumbList;
use RcsCodes\SEOTools\Schema\SchemaGraph;

// Standalone
$article = new Article();
$article->setHeadline('My Post')
        ->setAuthor('Jane Doe')
        ->setDatePublished('2024-06-01')
        ->setImage('https://example.com/img.jpg')
        ->setPublisher(['@type' => 'Organization', 'name' => 'Acme']);

echo $article->generate();

// Combining multiple types in one @graph block (recommended)
$graph = new SchemaGraph();
$graph->add($article)
      ->add((new BreadcrumbList)
          ->addItem('Home', 'https://example.com/')
          ->addItem('Blog', 'https://example.com/blog/')
          ->addItem('My Post', 'https://example.com/blog/my-post'));

echo $graph->generate();
```

---

### SchemaGraph

Combines multiple schema types into a single `@graph` block — the Google-recommended pattern for pages with more than one schema type.

```php
seo_schema()
    ->add($article)
    ->add($breadcrumb)
    ->add($faqPage);

echo seo_schema()->generate();
```

---

### Sitemap

```php
$sitemap = seo_sitemap();
$sitemap->addUrl('https://example.com/', 'daily', '1.0')
        ->addUrl('https://example.com/about', 'monthly', '0.8')
        ->addUrl('https://example.com/gallery', images: [
            ['loc' => 'https://example.com/photo.jpg', 'caption' => 'Gallery photo'],
        ])
        ->addUrl('https://example.com/video', video: [
            'thumbnail_loc' => 'https://example.com/thumb.jpg',
            'title'         => 'Demo video',
            'description'   => 'Watch the demo.',
            'duration'      => 180,
        ]);

return $sitemap->toResponse(); // sets Content-Type: application/xml
```

---

### SitemapIndex

```php
use RcsCodes\SEOTools\Technical\SitemapIndex;

$index = new SitemapIndex();
$index->addSitemap('https://example.com/sitemap-posts.xml', '2024-06-01')
      ->addSitemap('https://example.com/sitemap-pages.xml');

return $index->toResponse();
```

---

### RobotsTxt

```php
seo_robots()
    ->allow('*', '/')
    ->disallow('*', '/admin/')
    ->disallow('*', '/private/')
    ->crawlDelay('Googlebot', 1)
    ->blockAllAiTraining()      // Disallow: / for all known training bots
    ->addSitemap('https://example.com/sitemap.xml');

return seo_robots()->toResponse();
```

---

### HreflangManager

```php
seo_hreflang()
    ->addLanguage('en', 'https://example.com/')
    ->addLanguage('fr', 'https://fr.example.com/')
    ->addLanguage('de', 'https://de.example.com/')
    ->setDefault('https://example.com/');

echo seo_hreflang()->generate();
```

---

### RedirectHelper

```php
use RcsCodes\SEOTools\Technical\RedirectHelper;

// Register at boot time (e.g. in a service provider or BaseController)
RedirectHelper::register('/old-slug', '/new-slug');
RedirectHelper::register('/deleted-page', null, 410); // Gone

// In a route or filter
if (RedirectHelper::has($path)) {
    $entry = RedirectHelper::get($path);
    if ($entry['code'] === 410) {
        return RedirectHelper::gone();
    }
    return redirect()->to($entry['to'])->withCode($entry['code']);
}
```

---

### ResourceHints

```php
seo_hints()
    ->preconnect('https://fonts.googleapis.com')
    ->dnsPrefetch('https://cdn.example.com')
    ->preload('/fonts/inter.woff2', 'font', ['crossorigin' => 'anonymous'])
    ->prefetch('/js/chart.js');

echo seo_hints()->generate();
```

---

### AiBotManager

Fine-grained AI-crawler policy engine that outputs robots.txt rules, `X-Robots-Tag` headers, and meta robot content.

```php
use RcsCodes\SEOTools\Enterprise\AiBotManager;

$mgr = new AiBotManager();

// Named presets
$mgr->applyPreset('retrieval');   // allow crawling, block training datasets
$mgr->applyPreset('restrictive'); // block all AI bots
$mgr->applyPreset('permissive');  // allow everything

// Granular control
$mgr->allowRetrieval()->blockTraining();

// Per-bot override
$mgr->setBot('GPTBot', ['Allow: /public/', 'Disallow: /']);

// Apply to a robots.txt builder
foreach ($mgr->toRobotsTxtRules() as $bot => $rules) {
    foreach ($rules as $line) {
        // e.g. 'Disallow: /'
    }
}

// Apply as HTTP header
$mgr->applyHeaders(service('response'));

// Get meta robots content string
$content = $mgr->toMetaContent(); // e.g. "noai, noimageai"
```

---

### EEATMarkup

Structured data for E-E-A-T signals (Experience, Expertise, Authoritativeness, Trustworthiness).

```php
seo_eeat()
    ->setAuthor('Jane Doe', 'https://jane.com')
    ->setAuthorJobTitle('Senior Software Engineer')
    ->addAuthorSameAs('https://linkedin.com/in/janedoe')
    ->addAuthorSameAs('https://github.com/janedoe')
    ->addAuthorCredential('PhD, Computer Science — MIT')
    ->setOrganization('Acme Corp', 'https://acme.com', 'https://acme.com/logo.png')
    ->addOrganizationSameAs('https://twitter.com/acmecorp');

echo seo_eeat()->generateAuthorSchema();
echo seo_eeat()->generateOrganizationSchema();
```

---

### SEOMiddleware

Automatically injects a `<link rel="canonical">` tag into HTML responses that don't already have one. Register in `app/Config/Filters.php`:

```php
public array $globals = [
    'after' => [
        \RcsCodes\SEOTools\Enterprise\SEOMiddleware::class,
    ],
];
```

Enable/disable via `enterprise.middleware_auto_inject` in your config.

---

### MultiTenantManager

Per-domain config overrides for SaaS platforms.

```php
// app/Config/SEOTools.php
public array $tenants = [
    'brand-a.com' => [
        'meta'      => ['defaults' => ['title' => 'Brand A', 'separator' => ' — ']],
        'opengraph' => ['defaults' => ['site_name' => 'Brand A']],
    ],
    '*.brand-b.com' => [   // wildcard subdomain
        'meta' => ['defaults' => ['title' => 'Brand B']],
    ],
];

// In BaseController::initController()
(new MultiTenantManager())->apply();
```

---

## Global helpers

| Function | Returns | Description |
|---|---|---|
| `seo()` | `SEOTools` | Main aggregator |
| `seo_meta()` | `SEOMeta` | Meta tags component |
| `seo_og()` | `OpenGraph` | Open Graph component |
| `seo_twitter()` | `TwitterCard` | Twitter Card component |
| `seo_jsonld()` | `JsonLd` | Single JSON-LD block |
| `seo_jsonld_multi()` | `JsonLdMulti` | Multi-block JSON-LD |
| `seo_schema()` | `SchemaGraph` | Schema @graph builder |
| `seo_sitemap()` | `Sitemap` | XML sitemap builder |
| `seo_robots()` | `RobotsTxt` | robots.txt builder |
| `seo_hreflang()` | `HreflangManager` | Hreflang link builder |
| `seo_hints()` | `ResourceHints` | Resource hints builder |
| `seo_eeat()` | `EEATMarkup` | E-E-A-T schema builder |
| `seo_generate()` | `string` | Render all tags to HTML |
| `seo_reset()` | `void` | Reset shared instance |

---

## Macros

Extend `SEOTools` at runtime with Laravel-style macros:

```php
use RcsCodes\SEOTools\SEOTools;

SEOTools::macro('blogPost', function (string $title, string $desc, string $image) {
    /** @var SEOTools $this */
    $this->setTitle($title)
         ->setDescription($desc)
         ->addImages($image);
    $this->twitter()->setType('summary_large_image');
    return $this;
});

// In a controller:
seo()->blogPost('My Post', 'Great content.', 'https://example.com/img.jpg');
```

---

## Configuration reference

After running `php spark seotools:publish`, your `app/Config/SEOTools.php` exposes:

```php
// Meta tag defaults applied to every request
public array $meta = [
    'defaults' => [
        'title'       => null,          // site-wide default title
        'titleBefore' => true,          // page | site  (false = site | page)
        'separator'   => ' | ',
        'description' => null,
        'keywords'    => [],
        'robots'      => 'index, follow',
        'canonical'   => null,          // null = auto current_url(); false = disabled
    ],
    'webmaster_tags' => [
        'google'    => null,            // google-site-verification
        'bing'      => null,            // msvalidate.01
        'yandex'    => null,
        'pinterest' => null,
        'norton'    => null,
    ],
];

// Open Graph defaults
public array $opengraph = [
    'defaults' => [
        'url'       => null,            // null = auto current_url()
        'type'      => 'website',
        'site_name' => null,
        'images'    => [],
    ],
];

// Twitter Card defaults
public array $twitter = [
    'defaults' => [
        'card'    => 'summary',
        'site'    => null,              // @handle
        'creator' => null,
    ],
];

// AI-bot governance rules for RobotsTxt::applyAiBotPresets()
public array $robots = [
    'ai_bots' => [
        'GPTBot'    => 'retrieve',      // 'allow' | 'retrieve' | 'disallow'
        'ClaudeBot' => 'retrieve',
        'CCBot'     => 'disallow',
    ],
];

// Multi-tenant overrides (keyed by domain or *.wildcard)
public array $tenants = [];

// Enterprise settings
public array $enterprise = [
    'middleware_auto_inject' => true,
    'schema_validation'      => true,
    'debug_mode'             => false,
];
```

---

## Testing

```bash
composer install
composer test          # PHPUnit
composer analyse       # PHPStan level 8
composer cs-check      # PHP-CS-Fixer dry run
composer cs-fix        # Auto-fix code style
```

The test suite runs entirely without a CI4 application boot. See `tests/bootstrap.php` for the `TestConfig` helper that enables per-test configuration overrides.

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

---

## License

MIT. See [LICENSE.md](LICENSE.md).
