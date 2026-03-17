<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Tests\Integration;

use PHPUnit\Framework\TestCase;
use RcsCodes\SEOTools\Meta\JsonLd;
use RcsCodes\SEOTools\Meta\OpenGraph;
use RcsCodes\SEOTools\Meta\SEOMeta;
use RcsCodes\SEOTools\Meta\TwitterCard;
use RcsCodes\SEOTools\Schema\SchemaGraph;
use RcsCodes\SEOTools\Schema\Types\Article;
use RcsCodes\SEOTools\Schema\Types\BreadcrumbList;
use RcsCodes\SEOTools\Schema\Types\Offer;
use RcsCodes\SEOTools\Schema\Types\Product;
use RcsCodes\SEOTools\SEOTools;
use RcsCodes\SEOTools\Technical\RobotsTxt;
use RcsCodes\SEOTools\Technical\Sitemap;

/**
 * Integration tests — exercise complete real-world flows without mocks.
 *
 * These tests intentionally do NOT mock individual components; they wire
 * the full pipeline together exactly as a real controller would.
 */
class FullPipelineTest extends TestCase
{
    protected function setUp(): void
    {
        \TestConfig::reset();
    }

    protected function tearDown(): void
    {
        \TestConfig::reset();
        unset($_SERVER['TEST_CURRENT_URL']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Blog article page — the most common real-world SEO scenario
    // ─────────────────────────────────────────────────────────────────────────

    public function testBlogArticlePageOutputIsValid(): void
    {
        $_SERVER['TEST_CURRENT_URL'] = 'https://example.com/blog/hello-world';

        $seo = new SEOTools();
        $seo->setTitle('Hello World')
            ->setDescription('My first blog post about PHP.')
            ->setCanonical('https://example.com/blog/hello-world')
            ->addImages('https://example.com/img/hello.jpg')
        ;

        $seo->metatags()->setTitleDefault('My Blog')->setRobots('index, follow');
        $seo->opengraph()->setType('article')->setSiteName('My Blog');
        $seo->twitter()->setSite('@myblog')->setType('summary_large_image');

        $html = $seo->generate();

        // ── <title> ──────────────────────────────────────────────────────────
        $this->assertStringContainsString('<title>Hello World | My Blog</title>', $html);

        // ── Description on all three surfaces ────────────────────────────────
        $this->assertStringContainsString('name="description"', $html);
        $this->assertStringContainsString('property="og:description"', $html);
        $this->assertStringContainsString('name="twitter:description"', $html);

        // ── Canonical URL set once, propagated correctly ──────────────────────
        $this->assertSame(
            1,
            \substr_count($html, 'rel="canonical"'),
            'Exactly one canonical link tag expected',
        );
        $this->assertStringContainsString('href="https://example.com/blog/hello-world"', $html);

        // ── Open Graph ───────────────────────────────────────────────────────
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('property="og:type" content="article"', $html);
        $this->assertStringContainsString('property="og:image"', $html);

        // ── Twitter Card ─────────────────────────────────────────────────────
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $html);
        $this->assertStringContainsString('name="twitter:image"', $html);

        // ── JSON-LD ───────────────────────────────────────────────────────────
        $this->assertStringContainsString('<script type="application/ld+json">', $html);
        $this->assertStringContainsString('</script>', $html);
    }

    public function testBlogArticleOutputIsValidJson(): void
    {
        $seo = new SEOTools();
        $seo->setTitle('Test')->setDescription('Desc');

        $html = $seo->generate();

        // Extract and validate all JSON-LD blocks
        \preg_match_all(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $html,
            $matches,
        );

        $this->assertNotEmpty($matches[1], 'Expected at least one JSON-LD block');

        foreach ($matches[1] as $jsonBlock) {
            $decoded = \json_decode(\trim($jsonBlock), true);
            $this->assertNotNull($decoded, 'JSON-LD block must decode to valid JSON: ' . $jsonBlock);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Product page with rich schema graph
    // ─────────────────────────────────────────────────────────────────────────

    public function testProductPageSchemaGraph(): void
    {
        $product = new Product();
        $offer   = new Offer();
        $bc      = new BreadcrumbList();

        $offer->setPrice(29.99)->setPriceCurrency('GBP')->setAvailability('InStock');
        $product->setName('Super Widget')
            ->setDescription('The best widget.')
            ->setImage('https://example.com/widget.jpg')
            ->setOffers($offer)
            ->setSku('WGT-001')
        ;

        $bc->addItem('Home', 'https://example.com/')
            ->addItem('Products', 'https://example.com/products/')
            ->addItem('Super Widget', 'https://example.com/products/super-widget')
        ;

        $graph = new SchemaGraph();
        $graph->add($product)->add($bc);

        $output = $graph->generate();
        $data   = \json_decode(\strip_tags($output), true);

        $this->assertArrayHasKey('@graph', $data);
        $this->assertCount(2, $data['@graph']);

        $types = \array_column($data['@graph'], '@type');
        $this->assertContains('Product', $types);
        $this->assertContains('BreadcrumbList', $types);

        // Offer embedded correctly — no @context in nested item
        $productNode = $data['@graph'][\array_search('Product', $types, true)];
        $this->assertSame('Offer', $productNode['offers']['@type']);
        $this->assertArrayNotHasKey('@context', $productNode['offers']);

        // BreadcrumbList has 3 items
        $bcNode = $data['@graph'][\array_search('BreadcrumbList', $types, true)];
        $this->assertCount(3, $bcNode['itemListElement']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sitemap with all extension types
    // ─────────────────────────────────────────────────────────────────────────

    public function testSitemapXmlIsWellFormed(): void
    {
        $sitemap = new Sitemap();
        $sitemap
            ->addUrl('https://example.com/', 'daily', '1.0', '2024-06-01')
            ->addUrl('https://example.com/about', 'monthly', '0.8')
            ->addUrl('https://example.com/low-priority', 'yearly', '0')  // P0 fix
            ->addUrl(
                'https://example.com/gallery',
                images: [['loc' => 'https://example.com/hero.jpg', 'caption' => 'Hero']],
            )
            ->addUrl('https://example.com/video', video: [
                'thumbnail_loc' => 'https://example.com/thumb.jpg',
                'title'         => 'Demo',
                'description'   => 'Watch.',
            ])
        ;

        $xml = $sitemap->toXml();

        // Well-formed XML
        $dom = new \DOMDocument();
        $loaded = @$dom->loadXML($xml);
        $this->assertTrue($loaded, "Sitemap XML is not well-formed:\n" . $xml);

        // Namespace declarations present
        $this->assertStringContainsString('xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', $xml);
        $this->assertStringContainsString('xmlns:image=', $xml);
        $this->assertStringContainsString('xmlns:video=', $xml);

        // Priority 0 not dropped (P0 fix)
        $this->assertStringContainsString('<priority>0</priority>', $xml);

        // Image extension
        $this->assertStringContainsString('<image:image>', $xml);
        $this->assertStringContainsString('<image:caption>Hero</image:caption>', $xml);

        // Video extension
        $this->assertStringContainsString('<video:video>', $xml);

        $this->assertSame(5, $sitemap->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // robots.txt — combined manual rules + AI policy
    // ─────────────────────────────────────────────────────────────────────────

    public function testRobotsTxtCombinedOutput(): void
    {
        $robots = new RobotsTxt();
        $robots->allow('*', '/')
            ->disallow('*', '/admin/')
            ->disallow('*', '/private/')
            ->crawlDelay('Googlebot', 1)
            ->allowRetrievalBlockTraining()
            ->addSitemap('https://example.com/sitemap.xml')
        ;

        $output = $robots->generate();

        // Standard rules
        $this->assertStringContainsString('User-agent: *', $output);
        $this->assertStringContainsString('Allow: /', $output);
        $this->assertStringContainsString('Disallow: /admin/', $output);
        $this->assertStringContainsString('Crawl-delay: 1', $output);
        $this->assertStringContainsString('Sitemap: https://example.com/sitemap.xml', $output);

        // AI retrieval bots allowed
        $this->assertStringContainsString('GPTBot', $output);
        // Training-only bot blocked
        $this->assertStringContainsString('CCBot', $output);

        // Parse into blocks and verify structure
        $blocks = [];
        $current = null;

        foreach (\explode("\n", $output) as $line) {
            $line = \trim($line);

            if (\str_starts_with($line, 'User-agent:')) {
                $current = \substr($line, 12);
                $blocks[$current] = [];
            } elseif ($current && $line !== '') {
                $blocks[$current][] = $line;
            }
        }

        // CCBot must have Disallow: /
        $this->assertContains('Disallow: /', $blocks['CCBot'] ?? []);
        // GPTBot must have Allow: /
        $this->assertContains('Allow: /', $blocks['GPTBot'] ?? []);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Multi-component title propagation
    // ─────────────────────────────────────────────────────────────────────────

    public function testTitlePropagatedToAllSurfaces(): void
    {
        $seo = new SEOTools();
        $seo->setTitle('Propagation Test');

        $html = $seo->generate();

        // <title> tag
        $this->assertStringContainsString('<title>Propagation Test</title>', $html);
        // og:title
        $this->assertStringContainsString('content="Propagation Test"', $html);
        // twitter:title
        $surfaces = \substr_count($html, 'Propagation Test');
        // Appears in: <title>, og:title meta, twitter:title meta, JSON-LD name
        $this->assertGreaterThanOrEqual(4, $surfaces);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reset idempotency — calling reset() twice is safe
    // ─────────────────────────────────────────────────────────────────────────

    public function testDoubleResetIsSafe(): void
    {
        $seo = new SEOTools();
        $seo->setTitle('Before Reset');
        $seo->reset();
        $seo->reset(); // second reset must not throw

        $html = $seo->generate();
        $this->assertStringNotContainsString('Before Reset', $html);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // P0 regression: falsy description "0" must render
    // ─────────────────────────────────────────────────────────────────────────

    public function testFalsyDescriptionRegressionEndToEnd(): void
    {
        $seo = new SEOTools();
        $seo->setDescription('0');

        $html = $seo->generate();

        // Must appear in meta description
        $this->assertStringContainsString('content="0"', $html);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Minify mode — output contains no unintended whitespace
    // ─────────────────────────────────────────────────────────────────────────

    public function testMinifyModeFullPipeline(): void
    {
        $seo = new SEOTools();
        $seo->setTitle('Minify Test')
            ->setDescription('Minified description.')
            ->setCanonical('https://example.com/')
        ;

        $seo->metatags()->setRobots('index, follow');
        $seo->opengraph()->setType('website');
        $seo->twitter()->setType('summary');

        $minified = $seo->generate(minify: true);
        $pretty   = $seo->generate(minify: false);

        $this->assertStringNotContainsString("\n", $minified);
        $this->assertStringContainsString("\n", $pretty);
        $this->assertLessThan(\strlen($pretty), \strlen($minified));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Global helpers smoke test
    // ─────────────────────────────────────────────────────────────────────────

    public function testGlobalHelpersReturnCorrectTypes(): void
    {
        $this->assertInstanceOf(SEOTools::class, seo());
        $this->assertInstanceOf(SEOMeta::class, seo_meta());
        $this->assertInstanceOf(OpenGraph::class, seo_og());
        $this->assertInstanceOf(TwitterCard::class, seo_twitter());
        $this->assertInstanceOf(JsonLd::class, seo_jsonld());
    }

    public function testSeoGenerateHelperOutputsHtml(): void
    {
        seo_reset();
        seo()->setTitle('Helper Test');
        $html = seo_generate();
        $this->assertStringContainsString('<title>Helper Test</title>', $html);
        seo_reset();
    }
}
