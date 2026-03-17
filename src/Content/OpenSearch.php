<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Content;

use RcsCodes\SEOTools\Concerns\GeneratesHtml;

/**
 * OpenSearch
 *
 * Generates the <link> tag that points browsers and Google to an
 * OpenSearch description document, enabling the Sitelinks Search Box.
 *
 * You must also serve an OpenSearch description XML file at the given URL.
 * The helper `generateDescriptionXml()` builds a basic one for you.
 *
 * Usage:
 *   $os = new OpenSearch();
 *   $os->setTitle('Search My Site')
 *      ->setUrl('https://example.com/opensearch.xml');
 *   echo $os->generateLinkTag();
 *
 *   // Serve the description at the URL above:
 *   return $os->setSearchUrl('https://example.com/search?q={searchTerms}')
 *             ->toResponse();
 */
class OpenSearch
{
    use GeneratesHtml;

    protected ?string $title     = null;
    protected ?string $url       = null;
    protected ?string $searchUrl = null;
    protected string  $encoding  = 'UTF-8';

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * URL where the OpenSearch description XML file will be served.
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    /**
     * The actual search endpoint URL template.
     * Use `{searchTerms}` as the placeholder.
     *
     * Example: 'https://example.com/search?q={searchTerms}'
     */
    public function setSearchUrl(string $url): static
    {
        $this->searchUrl = $url;
        return $this;
    }

    public function setEncoding(string $encoding): static
    {
        $this->encoding = $encoding;
        return $this;
    }

    // ── Output ────────────────────────────────────────────────────────────────

    /**
     * Generate the <link> discovery tag for the <head>.
     */
    public function generateLinkTag(): string
    {
        if ($this->url === null) {
            return '';
        }

        return $this->linkTag('search', $this->url, [
            'type'  => 'application/opensearchdescription+xml',
            'title' => $this->title ?? 'Search',
        ]);
    }

    /**
     * Build the OpenSearch description XML body.
     */
    public function generateDescriptionXml(): string
    {
        $title     = htmlspecialchars($this->title ?? 'Search', ENT_XML1 | ENT_QUOTES);
        $searchUrl = htmlspecialchars($this->searchUrl ?? '', ENT_XML1 | ENT_QUOTES);

        return <<<XML
<?xml version="1.0" encoding="{$this->encoding}"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
    <ShortName>{$title}</ShortName>
    <Description>Search {$title}</Description>
    <InputEncoding>{$this->encoding}</InputEncoding>
    <Url type="text/html" template="{$searchUrl}"/>
</OpenSearchDescription>
XML;
    }

    /**
     * Return the description XML as a CI4 response (serve at $url endpoint).
     */
    public function toResponse(): \CodeIgniter\HTTP\ResponseInterface
    {
        /** @var \CodeIgniter\HTTP\ResponseInterface $response */
        $response = service('response');
        $response->setHeader('Content-Type', 'application/opensearchdescription+xml; charset=' . $this->encoding);
        $response->setBody($this->generateDescriptionXml());
        return $response;
    }

    public function reset(): static
    {
        $this->title     = null;
        $this->url       = null;
        $this->searchUrl = null;
        return $this;
    }
}
