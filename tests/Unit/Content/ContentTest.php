<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Tests\Unit\Content;

use PHPUnit\Framework\TestCase;
use RcsCodes\SEOTools\Content\AmpMeta;
use RcsCodes\SEOTools\Content\OpenSearch;
use RcsCodes\SEOTools\Content\ResourceHints;
use RcsCodes\SEOTools\Content\RssMeta;

/**
 * @covers \RcsCodes\SEOTools\Content\ResourceHints
 * @covers \RcsCodes\SEOTools\Content\AmpMeta
 * @covers \RcsCodes\SEOTools\Content\RssMeta
 * @covers \RcsCodes\SEOTools\Content\OpenSearch
 */
class ContentTest extends TestCase
{
    protected function setUp(): void
    {
        \TestConfig::reset();
    }

    protected function tearDown(): void
    {
        \TestConfig::reset();
    }

    // ── ResourceHints ─────────────────────────────────────────────────────────

    public function testPreloadGeneratesLinkTag(): void
    {
        $rh = new ResourceHints();
        $rh->preload('/fonts/inter.woff2', 'font', ['crossorigin' => 'anonymous', 'type' => 'font/woff2']);
        $html = $rh->generate();
        $this->assertStringContainsString('rel="preload"', $html);
        $this->assertStringContainsString('href="/fonts/inter.woff2"', $html);
        $this->assertStringContainsString('as="font"', $html);
        $this->assertStringContainsString('crossorigin="anonymous"', $html);
    }

    public function testPrefetchGeneratesLinkTag(): void
    {
        $rh = new ResourceHints();
        $rh->prefetch('/js/lazy-chart.js', 'script');
        $html = $rh->generate();
        $this->assertStringContainsString('rel="prefetch"', $html);
        $this->assertStringContainsString('as="script"', $html);
    }

    public function testPreconnect(): void
    {
        $rh = new ResourceHints();
        $rh->preconnect('https://fonts.googleapis.com', ['crossorigin' => '']);
        $html = $rh->generate();
        $this->assertStringContainsString('rel="preconnect"', $html);
        $this->assertStringContainsString('href="https://fonts.googleapis.com"', $html);
    }

    public function testDnsPrefetch(): void
    {
        $rh = new ResourceHints();
        $rh->dnsPrefetch('https://cdn.example.com');
        $html = $rh->generate();
        $this->assertStringContainsString('rel="dns-prefetch"', $html);
        $this->assertStringContainsString('href="https://cdn.example.com"', $html);
    }

    public function testModulePreload(): void
    {
        $rh = new ResourceHints();
        $rh->modulePreload('/js/app.mjs');
        $html = $rh->generate();
        $this->assertStringContainsString('rel="modulepreload"', $html);
    }

    public function testMultipleHintsOrdered(): void
    {
        $rh = new ResourceHints();
        $rh->preconnect('https://fonts.googleapis.com')
           ->dnsPrefetch('https://cdn.example.com')
           ->preload('/hero.jpg', 'image');
        $html = $rh->generate();
        $this->assertStringContainsString('preconnect', $html);
        $this->assertStringContainsString('dns-prefetch', $html);
        $this->assertStringContainsString('preload', $html);
        // Order: preconnect before dns-prefetch before preload
        $this->assertLessThan(strpos($html, 'dns-prefetch'), strpos($html, 'preconnect'));
    }

    public function testResourceHintsMinify(): void
    {
        $rh = new ResourceHints();
        $rh->preconnect('https://fonts.googleapis.com')->dnsPrefetch('https://cdn.example.com');
        $this->assertStringNotContainsString("\n", $rh->generate(minify: true));
    }

    public function testResourceHintsReset(): void
    {
        $rh = new ResourceHints();
        $rh->preconnect('https://fonts.googleapis.com');
        $rh->reset();
        $this->assertSame('', $rh->generate());
    }

    public function testPreloadWithoutAsAttr(): void
    {
        $rh = new ResourceHints();
        $rh->prefetch('/data.json');
        $html = $rh->generate();
        // No as="" attribute when $as is null
        $this->assertStringNotContainsString('as="', $html);
    }

    public function testPreloadXssEscaped(): void
    {
        $rh = new ResourceHints();
        $rh->preload('/font?v=1&t=2', 'font');
        $this->assertStringContainsString('&amp;', $rh->generate());
    }

    // ── AmpMeta ───────────────────────────────────────────────────────────────

    public function testGenerateForCanonicalEmitsAmpHtml(): void
    {
        $amp = new AmpMeta();
        $amp->setAmpUrl('https://example.com/article?amp=1');
        $html = $amp->generateForCanonical();
        $this->assertStringContainsString('rel="amphtml"', $html);
        $this->assertStringContainsString('href="https://example.com/article?amp=1"', $html);
    }

    public function testGenerateForAmpEmitsCanonical(): void
    {
        $amp = new AmpMeta();
        $amp->setCanonicalUrl('https://example.com/article');
        $html = $amp->generateForAmp();
        $this->assertStringContainsString('rel="canonical"', $html);
        $this->assertStringContainsString('href="https://example.com/article"', $html);
    }

    public function testGenerateForCanonicalEmptyWhenNoUrl(): void
    {
        $amp = new AmpMeta();
        $this->assertSame('', $amp->generateForCanonical());
    }

    public function testGenerateForAmpEmptyWhenNoUrl(): void
    {
        $amp = new AmpMeta();
        $this->assertSame('', $amp->generateForAmp());
    }

    public function testAmpMetaReset(): void
    {
        $amp = new AmpMeta();
        $amp->setAmpUrl('https://example.com/amp')
            ->setCanonicalUrl('https://example.com/');
        $amp->reset();
        $this->assertSame('', $amp->generateForCanonical());
        $this->assertSame('', $amp->generateForAmp());
    }

    public function testBothUrlsSetIndependently(): void
    {
        $amp = new AmpMeta();
        $amp->setAmpUrl('https://example.com/amp')
            ->setCanonicalUrl('https://example.com/');
        $canonical = $amp->generateForCanonical();
        $ampPage   = $amp->generateForAmp();
        $this->assertStringContainsString('amphtml', $canonical);
        $this->assertStringContainsString('canonical', $ampPage);
        // Each outputs exactly one link tag
        $this->assertSame(1, substr_count($canonical, '<link'));
        $this->assertSame(1, substr_count($ampPage, '<link'));
    }

    // ── RssMeta ───────────────────────────────────────────────────────────────

    public function testAddRssGeneratesAlternateLink(): void
    {
        $rss = new RssMeta();
        $rss->addRss('https://example.com/feed', 'Blog RSS');
        $html = $rss->generate();
        $this->assertStringContainsString('rel="alternate"', $html);
        $this->assertStringContainsString('application/rss+xml', $html);
        $this->assertStringContainsString('href="https://example.com/feed"', $html);
        $this->assertStringContainsString('title="Blog RSS"', $html);
    }

    public function testAddAtomGeneratesAlternateLink(): void
    {
        $rss = new RssMeta();
        $rss->addAtom('https://example.com/atom', 'Blog Atom');
        $html = $rss->generate();
        $this->assertStringContainsString('application/atom+xml', $html);
        $this->assertStringContainsString('title="Blog Atom"', $html);
    }

    public function testAddFeedCustomType(): void
    {
        $rss = new RssMeta();
        $rss->addFeed('https://example.com/json-feed', 'application/feed+json', 'JSON Feed');
        $html = $rss->generate();
        $this->assertStringContainsString('application/feed+json', $html);
        $this->assertStringContainsString('title="JSON Feed"', $html);
    }

    public function testMultipleFeedsRendered(): void
    {
        $rss = new RssMeta();
        $rss->addRss('https://example.com/rss')
            ->addAtom('https://example.com/atom');
        $html = $rss->generate();
        $this->assertSame(2, substr_count($html, '<link'));
    }

    public function testRssMetaMinify(): void
    {
        $rss = new RssMeta();
        $rss->addRss('https://example.com/rss')->addAtom('https://example.com/atom');
        $this->assertStringNotContainsString("\n", $rss->generate(minify: true));
    }

    public function testRssMetaReset(): void
    {
        $rss = new RssMeta();
        $rss->addRss('https://example.com/rss');
        $rss->reset();
        $this->assertSame('', $rss->generate());
    }

    public function testDefaultRssTitle(): void
    {
        $rss = new RssMeta();
        $rss->addRss('https://example.com/rss');
        $this->assertStringContainsString('RSS Feed', $rss->generate());
    }

    // ── OpenSearch ────────────────────────────────────────────────────────────

    public function testGenerateLinkTag(): void
    {
        $os = new OpenSearch();
        $os->setTitle('Search My Site')
           ->setUrl('https://example.com/opensearch.xml');
        $html = $os->generateLinkTag();
        $this->assertStringContainsString('rel="search"', $html);
        $this->assertStringContainsString('application/opensearchdescription+xml', $html);
        $this->assertStringContainsString('href="https://example.com/opensearch.xml"', $html);
        $this->assertStringContainsString('title="Search My Site"', $html);
    }

    public function testGenerateLinkTagReturnsEmptyWhenNoUrl(): void
    {
        $os = new OpenSearch();
        $os->setTitle('Search');
        $this->assertSame('', $os->generateLinkTag());
    }

    public function testDefaultTitleInLinkTag(): void
    {
        $os = new OpenSearch();
        $os->setUrl('https://example.com/opensearch.xml');
        // No title set — should fallback to 'Search'
        $this->assertStringContainsString('title="Search"', $os->generateLinkTag());
    }

    public function testGenerateDescriptionXmlStructure(): void
    {
        $os = new OpenSearch();
        $os->setTitle('My Site Search')
           ->setSearchUrl('https://example.com/search?q={searchTerms}');
        $xml = $os->generateDescriptionXml();
        $this->assertStringContainsString('<?xml version="1.0"', $xml);
        $this->assertStringContainsString('<ShortName>My Site Search</ShortName>', $xml);
        $this->assertStringContainsString('{searchTerms}', $xml);
        $this->assertStringContainsString('OpenSearchDescription', $xml);
    }

    public function testDescriptionXmlEscapesSpecialChars(): void
    {
        $os = new OpenSearch();
        $os->setTitle('My <Site> & Search');
        $xml = $os->generateDescriptionXml();
        $this->assertStringNotContainsString('<Site>', $xml);
        $this->assertStringContainsString('&lt;Site&gt;', $xml);
    }

    public function testOpenSearchReset(): void
    {
        $os = new OpenSearch();
        $os->setTitle('Search')->setUrl('https://example.com/os.xml');
        $os->reset();
        $this->assertSame('', $os->generateLinkTag());
    }

    public function testToResponseSetsContentType(): void
    {
        $os = new OpenSearch();
        $os->setTitle('Search')->setSearchUrl('https://example.com/s?q={searchTerms}');
        $response = $os->toResponse();
        $this->assertStringContainsString(
            'application/opensearchdescription+xml',
            $response->getHeaderLine('Content-Type')
        );
    }
}
