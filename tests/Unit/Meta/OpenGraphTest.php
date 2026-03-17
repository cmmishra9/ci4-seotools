<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Tests\Unit\Meta;

use PHPUnit\Framework\TestCase;
use RcsCodes\SEOTools\Meta\OpenGraph;

/**
 * @covers \RcsCodes\SEOTools\Meta\OpenGraph
 */
class OpenGraphTest extends TestCase
{
    private OpenGraph $og;

    protected function setUp(): void
    {
        \TestConfig::reset();
        $this->og = new OpenGraph();
    }

    protected function tearDown(): void
    {
        \TestConfig::reset();
        unset($_SERVER['TEST_CURRENT_URL']);
    }

    // ── Core properties ───────────────────────────────────────────────────────

    public function testSetTitle(): void
    {
        $this->og->setTitle('My Article');
        $this->assertStringContainsString('property="og:title" content="My Article"', $this->og->generate());
    }

    public function testSetDescription(): void
    {
        $this->og->setDescription('Some description');
        $this->assertStringContainsString('content="Some description"', $this->og->generate());
    }

    public function testSetDescriptionStripsHtml(): void
    {
        // P1 fix: OG description must strip tags like SEOMeta does
        $this->og->setDescription('<b>Bold</b> text');
        $this->assertStringContainsString('content="Bold text"', $this->og->generate());
    }

    public function testSetUrl(): void
    {
        $this->og->setUrl('https://example.com/article');
        $this->assertStringContainsString('content="https://example.com/article"', $this->og->generate());
    }

    public function testSetType(): void
    {
        $this->og->setType('article');
        $this->assertStringContainsString('property="og:type" content="article"', $this->og->generate());
    }

    public function testSetSiteName(): void
    {
        $this->og->setSiteName('Acme Blog');
        $this->assertStringContainsString('content="Acme Blog"', $this->og->generate());
    }

    public function testSetLocale(): void
    {
        $this->og->setLocale('en_US');
        $this->assertStringContainsString('content="en_US"', $this->og->generate());
    }

    // ── Double-prefix guard ───────────────────────────────────────────────────

    public function testAddPropertyStripsOgPrefix(): void
    {
        // P0 fix: caller passes 'og:title' → must not produce 'og:og:title'
        $this->og->addProperty('og:title', 'Prefixed');
        $html = $this->og->generate();
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringNotContainsString('og:og:', $html);
    }

    // ── Images ────────────────────────────────────────────────────────────────

    public function testAddImageSimple(): void
    {
        $this->og->addImage('https://example.com/img.jpg');
        $this->assertStringContainsString('property="og:image" content="https://example.com/img.jpg"', $this->og->generate());
    }

    public function testAddImageWithAttrs(): void
    {
        $this->og->addImage('https://example.com/img.jpg', ['width' => '1200', 'height' => '630', 'alt' => 'Hero']);
        $html = $this->og->generate();
        $this->assertStringContainsString('og:image:width', $html);
        $this->assertStringContainsString('og:image:height', $html);
        $this->assertStringContainsString('og:image:alt', $html);
    }

    public function testAddImagesMultiple(): void
    {
        $this->og->addImages(['https://example.com/a.jpg', 'https://example.com/b.jpg']);
        $html = $this->og->generate();
        $this->assertStringContainsString('a.jpg', $html);
        $this->assertStringContainsString('b.jpg', $html);
    }

    public function testHttpsImageAutoPopulatesSecureUrl(): void
    {
        $this->og->addImage('https://example.com/secure.jpg');
        $this->assertStringContainsString('og:image:secure_url', $this->og->generate());
    }

    public function testHttpImageDoesNotAutoPopulateSecureUrl(): void
    {
        $this->og->addImage('http://example.com/insecure.jpg');
        $this->assertStringNotContainsString('og:image:secure_url', $this->og->generate());
    }

    // ── Video ─────────────────────────────────────────────────────────────────

    public function testAddVideo(): void
    {
        $this->og->addVideo('https://example.com/video.mp4', ['type' => 'video/mp4', 'width' => '1280']);
        $html = $this->og->generate();
        $this->assertStringContainsString('property="og:video"', $html);
        $this->assertStringContainsString('og:video:type', $html);
    }

    // ── Audio ─────────────────────────────────────────────────────────────────

    public function testAddAudio(): void
    {
        $this->og->addAudio('https://example.com/track.mp3', ['type' => 'audio/mpeg']);
        $html = $this->og->generate();
        $this->assertStringContainsString('property="og:audio"', $html);
        $this->assertStringContainsString('og:audio:type', $html);
    }

    // ── Namespaces ────────────────────────────────────────────────────────────

    public function testSetArticle(): void
    {
        $this->og->setType('article')->setArticle([
            'published_time' => '2024-01-01',
            'author'         => 'Jane',
        ]);
        $html = $this->og->generate();
        $this->assertStringContainsString('article:published_time', $html);
        $this->assertStringContainsString('article:author', $html);
    }

    public function testSetArticleKeysNotDoubledPrefixed(): void
    {
        $this->og->setArticle(['article:tag' => 'php']);
        // should produce article:tag, not article:article:tag
        $html = $this->og->generate();
        $this->assertStringNotContainsString('article:article:', $html);
    }

    public function testSetProfile(): void
    {
        $this->og->setProfile(['first_name' => 'Jane', 'last_name' => 'Doe']);
        $this->assertStringContainsString('profile:first_name', $this->og->generate());
    }

    public function testSetPlace(): void
    {
        $this->og->setPlace(['location:latitude' => '51.5', 'location:longitude' => '-0.1']);
        $this->assertStringContainsString('place:', $this->og->generate());
    }

    // ── URL deferred resolution ───────────────────────────────────────────────

    public function testUrlAutoResolvedAtGenerateTime(): void
    {
        \TestConfig::merge(['opengraph' => ['defaults' => ['url' => null]]]);
        $_SERVER['TEST_CURRENT_URL'] = 'https://example.com/og-resolved';
        $og = new OpenGraph();
        $this->assertStringContainsString('og-resolved', $og->generate());
    }

    // ── Reset ─────────────────────────────────────────────────────────────────

    public function testReset(): void
    {
        $this->og->setTitle('X')->addImage('https://example.com/img.jpg');
        $this->og->reset();
        $html = $this->og->generate();
        $this->assertStringNotContainsString('content="X"', $html);
        $this->assertStringNotContainsString('og:image', $html);
    }

    // ── Minify ────────────────────────────────────────────────────────────────

    public function testMinify(): void
    {
        $this->og->setTitle('T')->setType('website');
        $this->assertStringNotContainsString("\n", $this->og->generate(minify: true));
    }
}
