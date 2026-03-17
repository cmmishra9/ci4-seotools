<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Technical;

use RcsCodes\SEOTools\Concerns\HasDefaults;
use RcsCodes\SEOTools\Contracts\SitemapInterface;

/**
 * Sitemap
 *
 * Builds standards-compliant XML sitemaps including image and video
 * extensions. Supports sitemap index files for large sites.
 *
 * Usage:
 *   $sitemap = new Sitemap();
 *   $sitemap->addUrl('https://example.com/', 'daily', '1.0');
 *   $sitemap->addUrl('https://example.com/about', 'monthly', '0.8');
 *   return $sitemap->toResponse();   // CI4 Response with correct Content-Type
 */
class Sitemap implements SitemapInterface
{
    use HasDefaults;

    /**
     * @var array<array{
     *   loc: string,
     *   lastmod: string|null,
     *   changefreq: string|null,
     *   priority: string|null,
     *   images: array<array<string,string>>,
     *   video: array<string,mixed>|null
     * }>
     */
    protected array $urls = [];

    protected string $defaultChangefreq = 'weekly';
    protected string $defaultPriority   = '0.5';

    public function __construct()
    {
        $this->bootConfig();
        $defaults = $this->config->sitemap['defaults'] ?? [];
        $this->defaultChangefreq = $defaults['changefreq'] ?? 'weekly';
        $this->defaultPriority   = $defaults['priority']   ?? '0.5';
    }

    /**
     * Add a URL entry.
     *
     * @param array<array<string,string>> $images [['loc'=>url,'caption'=>'…'], …]
     * @param array<string,mixed>|null $video
     */
    public function addUrl(
        string  $loc,
        ?string $changefreq = null,
        ?string $priority   = null,
        ?string $lastmod    = null,
        array   $images     = [],
        ?array  $video      = null,
    ): static {
        $this->urls[] = [
            'loc'        => $loc,
            'lastmod'    => $lastmod,
            'changefreq' => $changefreq ?? $this->defaultChangefreq,
            'priority'   => $priority   ?? $this->defaultPriority,
            'images'     => $images,
            'video'      => $video,
        ];

        return $this;
    }

    /**
     * Convenience: add multiple URLs at once.
     *
     * @param array<string> $urls
     */
    public function addUrls(array $urls, ?string $changefreq = null, ?string $priority = null): static
    {
        foreach ($urls as $url) {
            $this->addUrl($url, $changefreq, $priority);
        }

        return $this;
    }

    /**
     * Generate the XML sitemap string.
     */
    public function toXml(): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
              . "\n       xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\""
              . "\n       xmlns:video=\"http://www.google.com/schemas/sitemap-video/1.1\""
              . '>' . "\n";

        foreach ($this->urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . $this->xmlEscape($url['loc']) . "</loc>\n";

            if ($url['lastmod'] !== null && $url['lastmod'] !== '') {
                $xml .= '    <lastmod>' . $this->xmlEscape($url['lastmod']) . "</lastmod>\n";
            }

            if ($url['changefreq'] !== null && $url['changefreq'] !== '') {
                $xml .= '    <changefreq>' . $this->xmlEscape($url['changefreq']) . "</changefreq>\n";
            }

            if ($url['priority'] !== null && $url['priority'] !== '') {
                $xml .= '    <priority>' . $this->xmlEscape($url['priority']) . "</priority>\n";
            }

            // Image extensions
            foreach ($url['images'] as $img) {
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>' . $this->xmlEscape($img['loc']) . "</image:loc>\n";

                if (! empty($img['caption'])) {
                    $xml .= '      <image:caption>' . $this->xmlEscape($img['caption']) . "</image:caption>\n";
                }

                if (! empty($img['title'])) {
                    $xml .= '      <image:title>'   . $this->xmlEscape($img['title'])   . "</image:title>\n";
                }
                $xml .= "    </image:image>\n";
            }

            // Video extension
            if ($url['video']) {
                $v    = $url['video'];
                $xml .= "    <video:video>\n";
                $xml .= '      <video:thumbnail_loc>'  . $this->xmlEscape($v['thumbnail_loc'])  . "</video:thumbnail_loc>\n";
                $xml .= '      <video:title>'          . $this->xmlEscape($v['title'])           . "</video:title>\n";
                $xml .= '      <video:description>'    . $this->xmlEscape($v['description'])     . "</video:description>\n";

                if (! empty($v['content_loc'])) {
                    $xml .= '      <video:content_loc>'    . $this->xmlEscape($v['content_loc'])    . "</video:content_loc>\n";
                }

                if (! empty($v['player_loc'])) {
                    $xml .= '      <video:player_loc>'     . $this->xmlEscape($v['player_loc'])     . "</video:player_loc>\n";
                }

                if (! empty($v['duration'])) {
                    $xml .= '      <video:duration>'       . $this->xmlEscape((string)$v['duration']).'</video:duration>'."\n";
                }

                if (! empty($v['publication_date'])) {
                    $xml .= '      <video:publication_date>'.$this->xmlEscape($v['publication_date'])."</video:publication_date>\n";
                }
                $xml .= "    </video:video>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Output as a CI4 Response with the correct Content-Type.
     */
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
        return \count($this->urls);
    }

    public function reset(): static
    {
        $this->urls = [];

        return $this;
    }

    protected function xmlEscape(string $value): string
    {
        return \htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
