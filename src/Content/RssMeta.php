<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Content;

use RcsCodes\SEOTools\Concerns\GeneratesHtml;

/**
 * RssMeta
 *
 * Generates <link> tags that advertise RSS/Atom feed URLs to browsers
 * and feed readers.
 *
 * Usage:
 *   $rss = new RssMeta();
 *   $rss->addRss('https://example.com/feed', 'Blog RSS Feed')
 *       ->addAtom('https://example.com/atom', 'Blog Atom Feed');
 *   echo $rss->generate();
 */
class RssMeta
{
    use GeneratesHtml;

    /** @var array<array{type: string, href: string, title: string}> */
    protected array $feeds = [];

    public function addRss(string $url, string $title = 'RSS Feed'): static
    {
        $this->feeds[] = ['type' => 'application/rss+xml', 'href' => $url, 'title' => $title];
        return $this;
    }

    public function addAtom(string $url, string $title = 'Atom Feed'): static
    {
        $this->feeds[] = ['type' => 'application/atom+xml', 'href' => $url, 'title' => $title];
        return $this;
    }

    /**
     * Add a feed with a custom MIME type.
     */
    public function addFeed(string $url, string $type, string $title = ''): static
    {
        $this->feeds[] = ['type' => $type, 'href' => $url, 'title' => $title];
        return $this;
    }

    public function generate(bool $minify = false): string
    {
        $html = [];
        foreach ($this->feeds as $feed) {
            $attrs = ['type' => $feed['type']];
            if ($feed['title'] !== '') {
                $attrs['title'] = $feed['title'];
            }
            $html[] = $this->linkTag('alternate', $feed['href'], $attrs);
        }
        return $this->joinLines($html, $minify);
    }

    public function reset(): static
    {
        $this->feeds = [];
        return $this;
    }
}
