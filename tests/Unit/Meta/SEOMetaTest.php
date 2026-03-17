<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Tests\Unit\Meta;

use PHPUnit\Framework\TestCase;
use RcsCodes\SEOTools\Meta\SEOMeta;

/**
 * @covers \RcsCodes\SEOTools\Meta\SEOMeta
 */
class SEOMetaTest extends TestCase
{
    private SEOMeta $meta;

    protected function setUp(): void
    {
        \TestConfig::reset();
        $this->meta = new SEOMeta();
    }

    protected function tearDown(): void
    {
        \TestConfig::reset();
        unset($_SERVER['TEST_CURRENT_URL']);
    }

    // ── Title ─────────────────────────────────────────────────────────────────

    public function testSetTitleRendersInTag(): void
    {
        $this->meta->setTitle('Hello World');
        $this->assertStringContainsString('<title>Hello World</title>', $this->meta->generate());
    }

    public function testEmptyTitleTagNotEmitted(): void
    {
        // P0 fix: bare <title></title> must NOT be emitted when nothing is set
        $this->assertStringNotContainsString('<title></title>', $this->meta->generate());
    }

    public function testTitleDefaultAloneRendered(): void
    {
        $this->meta->setTitleDefault('My Site');
        $this->assertStringContainsString('<title>My Site</title>', $this->meta->generate());
    }

    public function testTitleCombinedWithDefault(): void
    {
        $this->meta->setTitle('Page')->setTitleDefault('Site');
        $this->assertStringContainsString('<title>Page | Site</title>', $this->meta->generate());
    }

    public function testTitleCustomSeparator(): void
    {
        $this->meta->setTitle('Page')->setTitleDefault('Site')->setTitleSeparator(' — ');
        $this->assertStringContainsString('<title>Page — Site</title>', $this->meta->generate());
    }

    public function testTitleAfterFlag(): void
    {
        $this->meta->setTitle('Page')->setTitleDefault('Site');
        $r = new \ReflectionProperty(SEOMeta::class, 'titleBefore');
        $r->setAccessible(true);
        $r->setValue($this->meta, false);
        $this->assertStringContainsString('<title>Site | Page</title>', $this->meta->generate());
    }

    public function testTitleStripsTags(): void
    {
        $this->meta->setTitle('<b>Bold</b> Title');
        $this->assertStringContainsString('<title>Bold Title</title>', $this->meta->generate());
    }

    public function testTitleFromConfigDefault(): void
    {
        \TestConfig::merge(['meta' => ['defaults' => ['title' => 'Config Title']]]);
        $this->assertStringContainsString('<title>Config Title</title>', (new SEOMeta())->generate());
    }

    public function testTitleXssEscaped(): void
    {
        $this->meta->setTitle('<script>xss</script>');
        $html = $this->meta->generate();
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('<title>xss</title>', $html);
    }

    // ── Description ──────────────────────────────────────────────────────────

    public function testDescriptionRendered(): void
    {
        $this->meta->setDescription('A great page.');
        $this->assertStringContainsString('content="A great page."', $this->meta->generate());
    }

    public function testDescriptionFalsyZeroRendered(): void
    {
        // P0 fix: string "0" must NOT be silently dropped
        $this->meta->setDescription('0');
        $this->assertStringContainsString('content="0"', $this->meta->generate());
    }

    public function testDescriptionStripsTags(): void
    {
        $this->meta->setDescription('<p>Clean</p>');
        $this->assertStringContainsString('content="Clean"', $this->meta->generate());
    }

    // ── Keywords ─────────────────────────────────────────────────────────────

    public function testKeywordsFromArray(): void
    {
        $this->meta->setKeywords(['php', 'seo', 'ci4']);
        $this->assertStringContainsString('content="php, seo, ci4"', $this->meta->generate());
    }

    public function testKeywordsFromString(): void
    {
        $this->meta->setKeywords('php, seo, ci4');
        $this->assertStringContainsString('content="php, seo, ci4"', $this->meta->generate());
    }

    public function testAddKeyword(): void
    {
        $this->meta->setKeywords(['php'])->addKeyword('seo')->addKeyword(['ci4', 'ci']);
        $this->assertStringContainsString('php, seo, ci4, ci', $this->meta->generate());
    }

    // ── Robots ────────────────────────────────────────────────────────────────

    public function testRobotsRendered(): void
    {
        $this->meta->setRobots('noindex, nofollow');
        $this->assertStringContainsString('content="noindex, nofollow"', $this->meta->generate());
    }

    public function testRobotsFalsyZeroRendered(): void
    {
        // P0 fix: robots value "0" must not be dropped
        $this->meta->setRobots('0');
        $this->assertStringContainsString('content="0"', $this->meta->generate());
    }

    // ── Canonical ─────────────────────────────────────────────────────────────

    public function testCanonicalExplicit(): void
    {
        $this->meta->setCanonical('https://example.com/page');
        $this->assertStringContainsString('href="https://example.com/page"', $this->meta->generate());
    }

    public function testCanonicalDisabledByDefault(): void
    {
        $this->assertStringNotContainsString('rel="canonical"', $this->meta->generate());
    }

    public function testCanonicalAutoResolvesAtGenerateTime(): void
    {
        // P1 fix: canonical = null → deferred to generate(), not constructor
        \TestConfig::merge(['meta' => ['defaults' => ['canonical' => null]]]);
        $_SERVER['TEST_CURRENT_URL'] = 'https://example.com/resolved-late';
        $meta = new SEOMeta();
        $this->assertStringContainsString('href="https://example.com/resolved-late"', $meta->generate());
    }

    public function testGetCanonicalResolvesAuto(): void
    {
        \TestConfig::merge(['meta' => ['defaults' => ['canonical' => null]]]);
        $_SERVER['TEST_CURRENT_URL'] = 'https://example.com/getter';
        $meta = new SEOMeta();
        $this->assertSame('https://example.com/getter', $meta->getCanonical());
    }

    public function testCanonicalXssEscaped(): void
    {
        $this->meta->setCanonical('https://example.com/?a=1&b=2');
        $this->assertStringContainsString('&amp;', $this->meta->generate());
    }

    // ── Prev / Next ───────────────────────────────────────────────────────────

    public function testPrevNextLinks(): void
    {
        $this->meta->setPrev('https://x.com/1')->setNext('https://x.com/3');
        $html = $this->meta->generate();
        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringContainsString('rel="next"', $html);
    }

    // ── Alternate languages ───────────────────────────────────────────────────

    public function testAddAlternateLanguage(): void
    {
        $this->meta->addAlternateLanguage('fr', 'https://fr.example.com/');
        $html = $this->meta->generate();
        $this->assertStringContainsString('hreflang="fr"', $html);
        $this->assertStringContainsString('href="https://fr.example.com/"', $html);
    }

    public function testSetAlternateLanguagesReplacesAll(): void
    {
        $this->meta->addAlternateLanguage('fr', 'https://fr.example.com/')
            ->setAlternateLanguage('de', 'https://de.example.com/')
        ;
        $html = $this->meta->generate();
        $this->assertStringNotContainsString('hreflang="fr"', $html);
        $this->assertStringContainsString('hreflang="de"', $html);
    }

    public function testAddAlternateLanguages(): void
    {
        $this->meta->addAlternateLanguages([
            ['lang' => 'en', 'url' => 'https://example.com/'],
            ['lang' => 'es', 'url' => 'https://es.example.com/'],
        ]);
        $html = $this->meta->generate();
        $this->assertStringContainsString('hreflang="en"', $html);
        $this->assertStringContainsString('hreflang="es"', $html);
    }

    // ── addMeta / removeMeta ──────────────────────────────────────────────────

    public function testAddMetaSingle(): void
    {
        $this->meta->addMeta('theme-color', '#ff0000');
        $this->assertStringContainsString('content="#ff0000"', $this->meta->generate());
    }

    public function testAddMetaArray(): void
    {
        $this->meta->addMeta(['foo' => 'bar', 'baz' => 'qux']);
        $html = $this->meta->generate();
        $this->assertStringContainsString('content="bar"', $html);
        $this->assertStringContainsString('content="qux"', $html);
    }

    public function testRemoveMeta(): void
    {
        $this->meta->addMeta('removable', 'yes')->removeMeta('removable');
        $this->assertStringNotContainsString('removable', $this->meta->generate());
    }

    // ── Webmaster tags ────────────────────────────────────────────────────────

    public function testGoogleVerificationTag(): void
    {
        \TestConfig::merge(['meta' => ['webmaster_tags' => ['google' => 'abc123']]]);
        $meta = new SEOMeta();
        $html = $meta->generate();
        $this->assertStringContainsString('google-site-verification', $html);
        $this->assertStringContainsString('abc123', $html);
    }

    public function testBingVerificationTag(): void
    {
        \TestConfig::merge(['meta' => ['webmaster_tags' => ['bing' => 'bing456']]]);
        $meta = new SEOMeta();
        $this->assertStringContainsString('msvalidate.01', $meta->generate());
    }

    // ── Minify ────────────────────────────────────────────────────────────────

    public function testMinifyProducesNoNewlines(): void
    {
        $this->meta->setTitle('T')->setDescription('D')->setCanonical('https://x.com');
        $this->assertStringNotContainsString("\n", $this->meta->generate(minify: true));
    }

    // ── Reset ─────────────────────────────────────────────────────────────────

    public function testResetClearsState(): void
    {
        $this->meta->setTitle('X')->setDescription('Y')->setCanonical('https://x.com');
        $this->meta->reset();
        $html = $this->meta->generate();
        $this->assertStringNotContainsString('X', $html);
        $this->assertStringNotContainsString('content="Y"', $html);
        $this->assertStringNotContainsString('rel="canonical"', $html);
    }

    // ── Getters ───────────────────────────────────────────────────────────────

    public function testGetTitle(): void
    {
        $this->meta->setTitle('Page')->setTitleDefault('Site');
        $this->assertSame('Page | Site', $this->meta->getTitle());
    }

    public function testGetDescription(): void
    {
        $this->meta->setDescription('Desc');
        $this->assertSame('Desc', $this->meta->getDescription());
    }

    public function testGetKeywords(): void
    {
        $this->meta->setKeywords(['a', 'b']);
        $this->assertSame(['a', 'b'], $this->meta->getKeywords());
    }

    public function testGetRobots(): void
    {
        $this->meta->setRobots('noindex');
        $this->assertSame('noindex', $this->meta->getRobots());
    }

    public function testGetTitleSeparator(): void
    {
        $this->meta->setTitleSeparator(' ~ ');
        $this->assertSame(' ~ ', $this->meta->getTitleSeparator());
    }
}
