<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Tests\Unit\Concerns;

use PHPUnit\Framework\TestCase;
use RcsCodes\SEOTools\Concerns\MacroableTrait;
use RcsCodes\SEOTools\Enterprise\SEOMiddleware;
use RcsCodes\SEOTools\SEOTools;
use RcsCodes\SEOTools\Traits\SEOToolsTrait;

/**
 * @covers \RcsCodes\SEOTools\Enterprise\SEOMiddleware
 * @covers \RcsCodes\SEOTools\Traits\SEOToolsTrait
 * @covers \RcsCodes\SEOTools\Concerns\GeneratesHtml
 * @covers \RcsCodes\SEOTools\Concerns\HasDefaults
 * @covers \RcsCodes\SEOTools\Concerns\MacroableTrait
 */
class ConcernsAndMiddlewareTest extends TestCase
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

    // ── SEOMiddleware (framework-free stub) ───────────────────────────────────

    /**
     * Build a minimal fake request/response pair that satisfies only the
     * method calls SEOMiddleware actually makes — no CI4 required.
     */
    private function makeResponse(string $body = '', string $contentType = 'text/html; charset=UTF-8'): \CodeIgniter\HTTP\ResponseInterface
    {
        return new class($body, $contentType) implements \CodeIgniter\HTTP\ResponseInterface {
            private string $body;
            private array  $headers;
            public function __construct(string $body, string $ct) {
                $this->body    = $body;
                $this->headers = ['Content-Type' => $ct];
            }
            public function getHeaderLine(string $k): string { return $this->headers[$k] ?? ''; }
            public function setHeader(string $k, $v): object { $this->headers[$k] = $v; return $this; }
            public function getBody(): string { return $this->body; }
            public function setBody($b): object { $this->body = (string)$b; return $this; }
            public function setContentType(string $m, string $c = 'UTF-8'): object { return $this; }
            public function setStatusCode(int $c, string $r = ''): object { return $this; }
            public function getStatusCode(): int { return 200; }
        };
    }

    private function makeRequest(): \CodeIgniter\HTTP\RequestInterface
    {
        return new class implements \CodeIgniter\HTTP\RequestInterface {
            public function getMethod(bool $upper = false): string { return 'GET'; }
            public function getHeaderLine(string $n): string { return ''; }
        };
    }

    public function testMiddlewareInjectsCanonicalWhenMissing(): void
    {
        $_SERVER['TEST_CURRENT_URL'] = 'https://example.com/page';
        $mw = new SEOMiddleware();
        $response = $this->makeResponse('<html><head><title>Test</title></head><body></body></html>');
        $result   = $mw->after($this->makeRequest(), $response);
        $this->assertStringContainsString('rel="canonical"', $result->getBody());
        $this->assertStringContainsString('https://example.com/page', $result->getBody());
    }

    public function testMiddlewareSkipsWhenCanonicalAlreadyPresent(): void
    {
        $body = '<html><head><link rel="canonical" href="https://example.com/existing"></head></html>';
        $mw   = new SEOMiddleware();
        $result = $mw->after($this->makeRequest(), $this->makeResponse($body));
        // Should not add a second canonical
        $this->assertSame(1, substr_count($result->getBody(), 'rel="canonical"'));
    }

    public function testMiddlewareSkipsNonHtmlResponse(): void
    {
        $body = '<?xml version="1.0"?><urlset></urlset>';
        $mw   = new SEOMiddleware();
        $result = $mw->after($this->makeRequest(), $this->makeResponse($body, 'application/xml'));
        $this->assertStringNotContainsString('rel="canonical"', $result->getBody());
    }

    public function testMiddlewareSkipsEmptyBody(): void
    {
        $mw     = new SEOMiddleware();
        $result = $mw->after($this->makeRequest(), $this->makeResponse(''));
        $this->assertSame('', $result->getBody());
    }

    public function testMiddlewareSkipsBodyWithoutHeadTag(): void
    {
        $mw     = new SEOMiddleware();
        $result = $mw->after($this->makeRequest(), $this->makeResponse('<div>No head tag</div>'));
        $this->assertStringNotContainsString('rel="canonical"', $result->getBody());
    }

    public function testMiddlewareDisabledViaConfig(): void
    {
        \TestConfig::merge(['enterprise' => ['middleware_auto_inject' => false]]);
        $_SERVER['TEST_CURRENT_URL'] = 'https://example.com/page';
        $mw   = new SEOMiddleware();
        $body = '<html><head><title>T</title></head><body></body></html>';
        $result = $mw->after($this->makeRequest(), $this->makeResponse($body));
        $this->assertStringNotContainsString('rel="canonical"', $result->getBody());
    }

    public function testMiddlewareBeforeReturnsNull(): void
    {
        $mw = new SEOMiddleware();
        $this->assertNull($mw->before($this->makeRequest()));
    }

    public function testCanonicalInjectedBeforeClosingHead(): void
    {
        $_SERVER['TEST_CURRENT_URL'] = 'https://example.com/';
        $mw   = new SEOMiddleware();
        $body = '<html><head><title>Test</title></head><body>content</body></html>';
        $result = $mw->after($this->makeRequest(), $this->makeResponse($body));
        // canonical must appear before </head>
        $pos_canonical = strpos($result->getBody(), 'rel="canonical"');
        $pos_head_close = strpos($result->getBody(), '</head>');
        $this->assertLessThan($pos_head_close, $pos_canonical);
    }

    // ── SEOToolsTrait ─────────────────────────────────────────────────────────

    public function testSeoToolsTraitLazyInit(): void
    {
        $controller = new class {
            use SEOToolsTrait;
        };
        // seo() should return an SEOTools instance
        $this->assertInstanceOf(SEOTools::class, $controller->seo());
    }

    public function testSeoToolsTraitReturnsSameInstance(): void
    {
        $controller = new class {
            use SEOToolsTrait;
        };
        $this->assertSame($controller->seo(), $controller->seo());
    }

    public function testSeoToolsTraitCanSetTitle(): void
    {
        $controller = new class {
            use SEOToolsTrait;
        };
        $controller->seo()->setTitle('Trait Title');
        $this->assertStringContainsString('Trait Title', $controller->seo()->generate());
    }

    public function testSeoToolsTraitIsolatedBetweenInstances(): void
    {
        $c1 = new class { use SEOToolsTrait; };
        $c2 = new class { use SEOToolsTrait; };
        $c1->seo()->setTitle('Controller One');
        $c2->seo()->setTitle('Controller Two');
        $this->assertStringContainsString('Controller One', $c1->seo()->generate());
        $this->assertStringNotContainsString('Controller One', $c2->seo()->generate());
    }

    // ── GeneratesHtml (exercised through SEOMeta/OpenGraph) ───────────────────

    public function testMetaNameTagEscapesQuotes(): void
    {
        // Indirectly tests GeneratesHtml::metaNameTag via SEOMeta
        $meta = new \RcsCodes\SEOTools\Meta\SEOMeta();
        $meta->addMeta('test', '"quoted value"');
        $html = $meta->generate();
        $this->assertStringContainsString('&quot;quoted value&quot;', $html);
    }

    public function testLinkTagExtraAttrs(): void
    {
        $meta = new \RcsCodes\SEOTools\Meta\SEOMeta();
        $meta->addAlternateLanguage('en', 'https://example.com/');
        $html = $meta->generate();
        $this->assertStringContainsString('hreflang="en"', $html);
    }

    public function testJoinLinesMinify(): void
    {
        $meta = new \RcsCodes\SEOTools\Meta\SEOMeta();
        $meta->setTitle('A')->setDescription('B')->setRobots('index');
        $minified = $meta->generate(minify: true);
        $pretty   = $meta->generate(minify: false);
        $this->assertStringNotContainsString("\n", $minified);
        $this->assertStringContainsString("\n", $pretty);
    }

    // ── HasDefaults (injection path) ──────────────────────────────────────────

    public function testSetConfigReplacesConfig(): void
    {
        $meta = new \RcsCodes\SEOTools\Meta\SEOMeta();
        \TestConfig::merge(['meta' => ['defaults' => ['title' => 'Injected Title']]]);
        $meta->setConfig(\TestConfig::get());
        // Re-apply defaults with the new config
        $meta->reset();
        $this->assertStringContainsString('Injected Title', $meta->generate());
    }

    // ── MacroableTrait (standalone) ───────────────────────────────────────────

    public function testMacroStaticRegistrationAndCall(): void
    {
        SEOTools::macro('testHelperMacro', function () {
            return 'macro-result';
        });
        $this->assertSame('macro-result', SEOTools::testHelperMacro());
    }

    public function testMacroInstanceCall(): void
    {
        SEOTools::macro('setPageTitle', function (string $t) {
            /** @var SEOTools $this */
            return $this->setTitle($t);
        });
        $seo = new SEOTools();
        $seo->setPageTitle('Instance Macro');
        $this->assertStringContainsString('Instance Macro', $seo->generate());
    }

    public function testHasMacroReturnsFalseForUnknown(): void
    {
        SEOTools::flushMacros();
        $this->assertFalse(SEOTools::hasMacro('nonExistentMacro'));
    }

    public function testFlushMacrosClearsAll(): void
    {
        SEOTools::macro('tempMacro', fn() => 'x');
        SEOTools::flushMacros();
        $this->assertFalse(SEOTools::hasMacro('tempMacro'));
    }

    public function testUndefinedMacroThrowsBadMethodCallException(): void
    {
        SEOTools::flushMacros();
        $this->expectException(\BadMethodCallException::class);
        $seo = new SEOTools();
        $seo->methodThatDoesNotExist();
    }
}
