<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Contracts;

/**
 * SitemapInterface
 *
 * Contract for the XML sitemap builder.
 */
interface SitemapInterface
{
    /**
     * @param array<array<string,string>> $images
     * @param array<string,mixed>|null $video
     */
    public function addUrl(
        string  $loc,
        ?string $changefreq = null,
        ?string $priority   = null,
        ?string $lastmod    = null,
        array   $images     = [],
        ?array  $video      = null,
    ): static;

    /** @param array<string> $urls */
    public function addUrls(array $urls, ?string $changefreq = null, ?string $priority = null): static;

    public function toXml(): string;

    public function toResponse(): \CodeIgniter\HTTP\ResponseInterface;

    public function count(): int;

    public function reset(): static;
}
