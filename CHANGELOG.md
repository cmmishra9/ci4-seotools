# Changelog

All notable changes to `rcscodes/ci4-seotools` are documented here.
Follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

_Changes queued for the next release._

---

## [0.2.0] — Pre-release candidate

### Added

**New schema types (Schema.org rich results)**
- `Course` — with provider, educational level, time required, pricing (free/paid), aggregate rating
- `Review` — item reviewed, star rating (with best/worst range), author, review body
- `NewsArticle` — headline, author, publisher (`NewsMediaOrganization`), article section, dateline, word count
- `SoftwareApplication` — OS, category, version, download/install URLs, file size, offers, aggregate rating, screenshots

**New contracts**
- `SchemaInterface` — formal contract for all `AbstractSchema` subclasses, enabling typed dependency injection
- `SitemapInterface` — formal contract for `Sitemap`, enabling mock-based integration testing

**`HasDefaults` trait improvements**
- `bootConfig()` now accepts an optional `?SEOToolsConfig $config` parameter for constructor injection
- New `setConfig(SEOToolsConfig $config): static` method enables post-construction config replacement (multi-tenant, tests)

**`MultiTenantManager` improvements**
- `apply()` now accepts an optional `?object $config` and returns the (possibly mutated) config — safe for chaining and testing
- `currentDomain()` prefers `$_SERVER['HTTP_HOST']` over `current_url()` parse — works correctly in CLI and test contexts

**`AiBotManager` improvements**
- `setBot(string $bot, string|array $policy)` — callers can now pass explicit directive-line arrays, not just `'allow'`/`'disallow'`
- `toRobotsTxtRules()` now returns flat `array<string, array<string>>` directive-line arrays for clean iteration with `RobotsTxt`
- `applyHeaders()` relaxed to `object` type hint + `method_exists` guard — usable in any framework, not just CI4

**`RedirectHelper` rewrite**
- `register(string $from, ?string $to, int $code = 301)` — `$to` is now nullable (supports 410 Gone entries)
- `get(string $from)` now returns `array{to: string|null, code: int}|null` instead of `?string`
- Chain-detection warning now safe against 410 entries

**Test infrastructure**
- `TestConfig` class in `tests/bootstrap.php` — resettable per-test, `merge()` for selective overrides
- `tearDown()` in every test class resets `TestConfig` and `$_SERVER` globals for full isolation
- `tests/Integration/` directory scaffolded

### Fixed

**P0 — Bugs that broke output in production**
- `SEOMeta::generate()`: falsy `if ($this->description)` silently dropped valid value `"0"` — fixed to `!== null && !== ''`
- `SEOMeta::generate()`: falsy `if ($this->robots)` silently dropped valid value `"0"` — same fix
- `SEOMeta::generate()`: `<title></title>` was emitted even when both `$title` and `$titleDefault` were null — now suppressed
- `AbstractSchema::toArray()`: nested `AbstractSchema` objects inside arrays (e.g. `setAuthor([$person1, $person2])`) were not converted via `toEmbeddedArray()` — added recursive `resolveValue()` unwrapper
- `BreadcrumbList::reset()`, `FAQPage::reset()`, `HowTo::reset()`: private item arrays were not cleared — each now overrides `reset()` and calls `parent::reset()`
- `Sitemap::toXml()`: `if ($url['priority'])` dropped valid priority `"0"` — fixed to `!== null && !== ''` for `priority`, `changefreq`, `lastmod`
- `OpenGraph::addProperty()` / `TwitterCard::addValue()`: callers passing `"og:title"` or `"twitter:card"` produced doubled prefixes (`og:og:title`) — both methods now strip the prefix if present
- `AbstractSchema::generate()`, `SchemaGraph::generate()`, `JsonLd::generate()`: `json_encode` without `JSON_THROW_ON_ERROR` silently returned `false` on unencodable values — flag added to all three

**P1 — Wrong behaviour in real deployments**
- `SEOMeta::applyDefaults()` called `current_url()` at construction time (wrong URL in CLI/queue contexts) — replaced with `'__auto__'` sentinel resolved lazily in `generate()` and `getCanonical()`
- `OpenGraph::applyDefaults()` same deferred-resolution fix for `og:url`
- `SEOTools` and `helpers.php`: `ResourceHints` was imported from `Technical\` namespace — corrected to `Content\`
- `OpenGraph::setDescription()` did not strip HTML tags — now consistent with `SEOMeta::setDescription()`
- `JsonLdMulti::isEmpty()` only checked the currently-active group — now iterates all groups
- `AbstractSchema` had no `reset()` method — added, used by `BreadcrumbList`, `FAQPage`, `HowTo` via `parent::reset()`

---

## [0.1.0] — 2024-06-01 (initial build)

### Added
- `SEOMeta` — title (with separator + before/after), description, keywords, robots, canonical, prev/next pagination, hreflang alternates, webmaster verification tags (Google, Bing, Yandex, Pinterest, Norton, Alexa)
- `OpenGraph` — all `og:*` core properties, images with attributes (secure_url, type, width, height, alt), video, audio, all namespace helpers (article, book, profile, music, video, place)
- `TwitterCard` — summary, summary_large_image, app, player card types; site, creator, image, image:alt
- `JsonLd` — single-block `application/ld+json` with type, title, description, URL, images, arbitrary values
- `JsonLdMulti` — multiple JSON-LD blocks per page with active-group selection
- Schema types: `Article`, `Product`+`Offer`, `BreadcrumbList`, `FAQPage`, `HowTo`, `Event`, `Organization`, `LocalBusiness`, `JobPosting`, `Recipe`, `VideoObject`
- `SchemaGraph` — `@graph` multi-type combined block with per-item validation
- `Sitemap` — XML sitemap with image extension (caption, title) and video extension (thumbnail, duration, player)
- `SitemapIndex` — sitemap index XML
- `RobotsTxt` — fluent builder with AI-bot governance: `blockAllAiTraining()`, `allowRetrievalBlockTraining()`, `applyAiBotPresets()`
- `HreflangManager` — multi-language alternate links with `x-default`
- `RedirectHelper` — 301/302/410 redirect management with chain detection
- `ResourceHints` — preload, prefetch, preconnect, dns-prefetch, modulepreload
- `AmpMeta` — canonical-to-AMP and AMP-to-canonical link pairs
- `RssMeta` — RSS 2.0 and Atom feed discovery links
- `OpenSearch` — OpenSearch description XML and `<link>` tag
- `SEOMiddleware` — CI4 filter for auto-injecting canonical tags on HTML responses
- `MultiTenantManager` — per-domain config overrides with wildcard subdomain matching
- `AiBotManager` — retrieval vs training policy engine with named presets (permissive / retrieval / restrictive)
- `EEATMarkup` — E-E-A-T author + organisation schema (Person, ProfilePage, hasCredential, sameAs)
- `SpeakableMarkup` — CSS selector + XPath speakable schema for voice/AI assistants
- `MacroableTrait` — Laravel-style runtime macro extension for `SEOTools`
- `SEOToolsTrait` — `$this->seo()` controller mixin
- Global helper functions: `seo()`, `seo_meta()`, `seo_og()`, `seo_twitter()`, `seo_jsonld()`, `seo_jsonld_multi()`, `seo_schema()`, `seo_sitemap()`, `seo_robots()`, `seo_hreflang()`, `seo_hints()`, `seo_eeat()`, `seo_generate()`, `seo_reset()`
- `php spark seotools:publish` — publishes config and view partials to the app
- GitHub Actions CI matrix: PHP 8.1–8.4 × CI4 4.4/4.5, PHPStan level 8, PHP-CS-Fixer
- GitHub Actions auto-release workflow on tag push

[Unreleased]: https://github.com/rcscodes/ci4-seotools/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/rcscodes/ci4-seotools/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/rcscodes/ci4-seotools/releases/tag/v0.1.0
