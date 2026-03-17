<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Technical;

/**
 * SitemapIndex
 *
 * Generates a sitemap index file pointing to multiple child sitemaps.
 * Required when a single sitemap exceeds 50,000 URLs or 50 MB.
 *
 * Usage:
 *   $index = new SitemapIndex();
 *   $index->addSitemap('https://example.com/sitemap-posts.xml', '2025-01-01')
 *         ->addSitemap('https://example.com/sitemap-pages.xml')
 *         ->addSitemap('https://example.com/sitemap-products.xml');
 *   return $index->toResponse();
 */
class SitemapIndex
{
    /** @var array<array{loc: string, lastmod: string|null}> */
    protected array $sitemaps = [];

    public function addSitemap(string $url, ?string $lastmod = null): static
    {
        $this->sitemaps[] = ['loc' => $url, 'lastmod' => $lastmod];

        return $this;
    }

    /** @param array<string> $urls */
    public function addSitemaps(array $urls): static
    {
        foreach ($urls as $url) {
            $this->addSitemap($url);
        }

        return $this;
    }

    public function toXml(): string
    {
        $lines = [];
        $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $lines[] = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($this->sitemaps as $entry) {
            $lines[] = '    <sitemap>';
            $lines[] = '        <loc>' . \htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES) . '</loc>';

            if ($entry['lastmod'] !== null) {
                $lines[] = '        <lastmod>' . $entry['lastmod'] . '</lastmod>';
            }
            $lines[] = '    </sitemap>';
        }

        $lines[] = '</sitemapindex>';

        return \implode("\n", $lines);
    }

    public function toResponse(): \CodeIgniter\HTTP\ResponseInterface
    {
        /** @var \CodeIgniter\HTTP\ResponseInterface $response */
        $response = service('response');
        $response->setHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->setBody($this->toXml());

        return $response;
    }

    public function count(): int
    {
        return \count($this->sitemaps);
    }

    public function reset(): static
    {
        $this->sitemaps = [];

        return $this;
    }
}
