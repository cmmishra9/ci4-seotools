<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RcsCodes\SEOTools\Concerns\MacroableTrait;
use RcsCodes\SEOTools\Meta\JsonLd;
use RcsCodes\SEOTools\Meta\JsonLdMulti;
use RcsCodes\SEOTools\Meta\OpenGraph;
use RcsCodes\SEOTools\Meta\SEOMeta;
use RcsCodes\SEOTools\Meta\TwitterCard;
use RcsCodes\SEOTools\SEOTools;

/**
 * @covers \RcsCodes\SEOTools\SEOTools
 * @covers \RcsCodes\SEOTools\Concerns\MacroableTrait
 */
class SEOToolsTest extends TestCase
{
    private SEOTools $seo;

    protected function setUp(): void
    {
        \TestConfig::reset();
        $this->seo = new SEOTools();
    }

    protected function tearDown(): void
    {
        \TestConfig::reset();
    }

    // ── Component accessors ───────────────────────────────────────────────────

    public function testMetatagsAccessor(): void
    {
        $this->assertInstanceOf(SEOMeta::class, $this->seo->metatags());
    }

    public function testOpengraphAccessor(): void
    {
        $this->assertInstanceOf(OpenGraph::class, $this->seo->opengraph());
    }

    public function testTwitterAccessor(): void
    {
        $this->assertInstanceOf(TwitterCard::class, $this->seo->twitter());
    }

    public function testJsonLdAccessor(): void
    {
        $this->assertInstanceOf(JsonLd::class, $this->seo->jsonLd());
    }

    public function testJsonLdMultiAccessor(): void
    {
        $this->assertInstanceOf(JsonLdMulti::class, $this->seo->jsonLdMulti());
    }

    public function testSameInstanceReturnedEachTime(): void
    {
        $this->assertSame($this->seo->metatags(), $this->seo->metatags());
        $this->assertSame($this->seo->opengraph(), $this->seo->opengraph());
    }

    // ── Proxy setters ─────────────────────────────────────────────────────────

    public function testSetTitlePropagatesEveryWhere(): void
    {
        $this->seo->setTitle('My Title');
        $this->assertStringContainsString('My Title', $this->seo->metatags()->generate());
        $this->assertStringContainsString('My Title', $this->seo->opengraph()->generate());
        $this->assertStringContainsString('My Title', $this->seo->twitter()->generate());
    }

    public function testSetDescriptionPropagates(): void
    {
        $this->seo->setDescription('Great desc.');
        $this->assertStringContainsString('Great desc.', $this->seo->metatags()->generate());
        $this->assertStringContainsString('Great desc.', $this->seo->opengraph()->generate());
    }

    public function testSetCanonicalPropagates(): void
    {
        $this->seo->setCanonical('https://example.com/page');
        $this->assertStringContainsString('https://example.com/page', $this->seo->metatags()->generate());
        $this->assertStringContainsString('https://example.com/page', $this->seo->opengraph()->generate());
    }

    public function testAddImagesPropagates(): void
    {
        $this->seo->addImages('https://example.com/img.jpg');
        $ogHtml = $this->seo->opengraph()->generate();
        $this->assertStringContainsString('og:image', $ogHtml);
    }

    public function testAddImagesArray(): void
    {
        $this->seo->addImages(['https://example.com/a.jpg', 'https://example.com/b.jpg']);
        $html = $this->seo->opengraph()->generate();
        $this->assertStringContainsString('a.jpg', $html);
        $this->assertStringContainsString('b.jpg', $html);
    }

    // ── generate() ────────────────────────────────────────────────────────────

    public function testGenerateContainsAllSections(): void
    {
        $this->seo->setTitle('Test Page')
            ->setDescription('Test description.')
        ;
        $html = $this->seo->generate();
        $this->assertStringContainsString('<title>', $html);
        $this->assertStringContainsString('og:title', $html);
        $this->assertStringContainsString('twitter:title', $html);
    }

    public function testGenerateMinify(): void
    {
        $this->seo->setTitle('T')->setDescription('D');
        $this->assertStringNotContainsString("\n", $this->seo->generate(minify: true));
    }

    // ── getTitle ──────────────────────────────────────────────────────────────

    public function testGetTitleFull(): void
    {
        $this->seo->setTitle('Page');
        $this->seo->metatags()->setTitleDefault('Site');
        $this->assertSame('Page | Site', $this->seo->getTitle());
    }

    public function testGetTitleSession(): void
    {
        $this->seo->setTitle('Session Title');
        $this->assertSame('Session Title', $this->seo->getTitle(session: true));
    }

    // ── Reset ─────────────────────────────────────────────────────────────────

    public function testResetClearsAllComponents(): void
    {
        $this->seo->setTitle('X')->setDescription('Y');
        $this->seo->reset();
        $html = $this->seo->generate();
        $this->assertStringNotContainsString('content="X"', $html);
        $this->assertStringNotContainsString('content="Y"', $html);
    }

    // ── MacroableTrait ────────────────────────────────────────────────────────

    public function testMacroRegistered(): void
    {
        SEOTools::macro('addBlogDefaults', function () {
            /** @var SEOTools $this */
            $this->metatags()->setRobots('index, follow');

            return $this;
        });
        $this->assertTrue(SEOTools::hasMacro('addBlogDefaults'));
    }

    public function testMacroCallable(): void
    {
        SEOTools::macro('setTestTitle', function (string $title) {
            /** @var SEOTools $this */
            $this->setTitle($title);

            return $this;
        });
        $this->seo->setTestTitle('Macro Title');
        $this->assertStringContainsString('Macro Title', $this->seo->generate());
    }

    public function testMacroFlush(): void
    {
        SEOTools::macro('testMacro', fn () => 'ok');
        SEOTools::flushMacros();
        $this->assertFalse(SEOTools::hasMacro('testMacro'));
    }

    public function testUndefinedMethodThrows(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->seo->nonExistentMethod();
    }
}
