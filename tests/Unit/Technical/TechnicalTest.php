<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Tests\Unit\Technical;

use PHPUnit\Framework\TestCase;
use RcsCodes\SEOTools\Technical\HreflangManager;
use RcsCodes\SEOTools\Technical\RedirectHelper;
use RcsCodes\SEOTools\Technical\RobotsTxt;
use RcsCodes\SEOTools\Technical\Sitemap;
use RcsCodes\SEOTools\Technical\SitemapIndex;

/**
 * @covers \RcsCodes\SEOTools\Technical\Sitemap
 * @covers \RcsCodes\SEOTools\Technical\SitemapIndex
 * @covers \RcsCodes\SEOTools\Technical\RobotsTxt
 * @covers \RcsCodes\SEOTools\Technical\HreflangManager
 * @covers \RcsCodes\SEOTools\Technical\RedirectHelper
 */
class TechnicalTest extends TestCase
{
    protected function setUp(): void
    {
        \TestConfig::reset();
        RedirectHelper::flush();
    }

    protected function tearDown(): void
    {
        \TestConfig::reset();
        RedirectHelper::flush();
    }

    // ── Sitemap ───────────────────────────────────────────────────────────────

    public function testSitemapProducesXml(): void
    {
        $sitemap = new Sitemap();
        $sitemap->addUrl('https://example.com/', 'daily', '1.0');
        $xml = $sitemap->toXml();
        $this->assertStringContainsString('<?xml version="1.0"', $xml);
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString('<loc>https://example.com/</loc>', $xml);
    }

    public function testSitemapPriorityZeroNotDropped(): void
    {
        // P0 fix: priority "0" must not be dropped by falsy check
        $sitemap = new Sitemap();
        $sitemap->addUrl('https://example.com/low', 'yearly', '0');
        $xml = $sitemap->toXml();
        $this->assertStringContainsString('<priority>0</priority>', $xml);
    }

    public function testSitemapChangefreqRendered(): void
    {
        $sitemap = new Sitemap();
        $sitemap->addUrl('https://example.com/', 'daily');
        $this->assertStringContainsString('<changefreq>daily</changefreq>', $sitemap->toXml());
    }

    public function testSitemapLastmodRendered(): void
    {
        $sitemap = new Sitemap();
        $sitemap->addUrl('https://example.com/', lastmod: '2024-01-15');
        $this->assertStringContainsString('<lastmod>2024-01-15</lastmod>', $sitemap->toXml());
    }

    public function testSitemapImageExtension(): void
    {
        $sitemap = new Sitemap();
        $sitemap->addUrl(
            'https://example.com/gallery',
            images: [['loc' => 'https://example.com/photo.jpg', 'caption' => 'A photo']]
        );
        $xml = $sitemap->toXml();
        $this->assertStringContainsString('<image:image>', $xml);
        $this->assertStringContainsString('<image:loc>', $xml);
        $this->assertStringContainsString('<image:caption>A photo</image:caption>', $xml);
    }

    public function testSitemapVideoExtension(): void
    {
        $sitemap = new Sitemap();
        $sitemap->addUrl('https://example.com/video', video: [
            'thumbnail_loc' => 'https://example.com/thumb.jpg',
            'title'         => 'My Video',
            'description'   => 'Watch this.',
            'duration'      => 120,
        ]);
        $this->assertStringContainsString('<video:video>', $sitemap->toXml());
        $this->assertStringContainsString('<video:duration>120</video:duration>', $sitemap->toXml());
    }

    public function testSitemapAddUrls(): void
    {
        $sitemap = new Sitemap();
        $sitemap->addUrls(['https://example.com/a', 'https://example.com/b', 'https://example.com/c']);
        $this->assertSame(3, $sitemap->count());
    }

    public function testSitemapXssEscaped(): void
    {
        $sitemap = new Sitemap();
        $sitemap->addUrl('https://example.com/?a=1&b=2');
        $this->assertStringContainsString('&amp;', $sitemap->toXml());
    }

    public function testSitemapReset(): void
    {
        $sitemap = new Sitemap();
        $sitemap->addUrl('https://example.com/');
        $sitemap->reset();
        $this->assertSame(0, $sitemap->count());
    }

    // ── SitemapIndex ──────────────────────────────────────────────────────────

    public function testSitemapIndex(): void
    {
        $index = new SitemapIndex();
        $index->addSitemap('https://example.com/sitemap-posts.xml', '2024-01-01')
              ->addSitemap('https://example.com/sitemap-pages.xml');
        $xml = $index->toXml();
        $this->assertStringContainsString('<sitemapindex', $xml);
        $this->assertStringContainsString('sitemap-posts.xml', $xml);
        $this->assertStringContainsString('<lastmod>2024-01-01</lastmod>', $xml);
        $this->assertSame(2, $index->count());
    }

    public function testSitemapIndexReset(): void
    {
        $index = new SitemapIndex();
        $index->addSitemap('https://example.com/sm.xml');
        $index->reset();
        $this->assertSame(0, $index->count());
    }

    // ── RobotsTxt ─────────────────────────────────────────────────────────────

    public function testRobotsTxtBasicRules(): void
    {
        $robots = new RobotsTxt();
        $robots->allow('*', '/')->disallow('*', '/admin')->addSitemap('https://example.com/sitemap.xml');
        $out = $robots->generate();
        $this->assertStringContainsString('User-agent: *', $out);
        $this->assertStringContainsString('Allow: /', $out);
        $this->assertStringContainsString('Disallow: /admin', $out);
        $this->assertStringContainsString('Sitemap: https://example.com/sitemap.xml', $out);
    }

    public function testRobotsTxtCrawlDelay(): void
    {
        $robots = new RobotsTxt();
        $robots->crawlDelay('*', 2);
        $this->assertStringContainsString('Crawl-delay: 2', $robots->generate());
    }

    public function testBlockAllAiBots(): void
    {
        $robots = new RobotsTxt();
        $robots->blockAllAiTraining();
        $out = $robots->generate();
        $this->assertStringContainsString('GPTBot', $out);
        $this->assertStringContainsString('Disallow: /', $out);
    }

    public function testAllowRetrievalBlockTraining(): void
    {
        $robots = new RobotsTxt();
        $robots->allowRetrievalBlockTraining();
        $out = $robots->generate();
        // CCBot (training only) should be blocked
        $this->assertStringContainsString('CCBot', $out);
    }

    public function testRobotsTxtReset(): void
    {
        $robots = new RobotsTxt();
        $robots->disallow('*', '/secret');
        $robots->reset();
        $this->assertStringNotContainsString('/secret', $robots->generate());
    }

    // ── HreflangManager ───────────────────────────────────────────────────────

    public function testHreflangMultipleLanguages(): void
    {
        $hm = new HreflangManager();
        $hm->addLanguage('en', 'https://example.com/')
           ->addLanguage('fr', 'https://fr.example.com/')
           ->setDefault('https://example.com/');
        $html = $hm->generate();
        $this->assertStringContainsString('hreflang="en"', $html);
        $this->assertStringContainsString('hreflang="fr"', $html);
        $this->assertStringContainsString('hreflang="x-default"', $html);
    }

    public function testHreflangReset(): void
    {
        $hm = new HreflangManager();
        $hm->addLanguage('en', 'https://example.com/');
        $hm->reset();
        $this->assertSame('', $hm->generate());
    }

    // ── RedirectHelper ────────────────────────────────────────────────────────

    public function testRegisterAndRetrieve(): void
    {
        RedirectHelper::register('/old', '/new');
        $this->assertTrue(RedirectHelper::has('/old'));
        $this->assertSame('/new', RedirectHelper::get('/old')['to']);
    }

    public function testPermanentRedirectCode(): void
    {
        RedirectHelper::register('/old', '/new', 301);
        $this->assertSame(301, RedirectHelper::get('/old')['code']);
    }

    public function testGoneCode(): void
    {
        RedirectHelper::register('/deleted', null, 410);
        $this->assertSame(410, RedirectHelper::get('/deleted')['code']);
    }

    public function testRedirectAll(): void
    {
        RedirectHelper::register('/a', '/b');
        RedirectHelper::register('/c', '/d');
        $this->assertCount(2, RedirectHelper::all());
    }

    public function testFlushClearsAll(): void
    {
        RedirectHelper::register('/x', '/y');
        RedirectHelper::flush();
        $this->assertEmpty(RedirectHelper::all());
    }
}
