<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Enterprise;

/**
 * EEATMarkup
 *
 * Builds Experience-Expertise-Authoritativeness-Trustworthiness (E-E-A-T)
 * structured data signals that Google uses to assess content quality.
 *
 * Generates:
 *  - Author Person schema with credentials, sameAs links to authority sites
 *  - Organization schema with verifiable SameAs links (LinkedIn, Wikipedia,
 *    Crunchbase, Wikidata, etc.)
 *  - Publisher block for Article schemas
 *
 * Usage:
 *   $eeat = new EEATMarkup();
 *   $eeat->setAuthor('Jane Smith', 'https://example.com/team/jane')
 *        ->addAuthorCredential('PhD in Computer Science')
 *        ->addAuthorSameAs('https://linkedin.com/in/janesmith')
 *        ->addAuthorSameAs('https://twitter.com/janesmith');
 *   echo $eeat->generateAuthorSchema();
 */
class EEATMarkup
{
    // ── Author ────────────────────────────────────────────────────────────────

    protected ?string $authorName = null;
    protected ?string $authorUrl  = null;
    /** @var array<string> */
    protected array $authorSameAs      = [];
    /** @var array<string> */
    protected array $authorCredentials = [];
    protected ?string $authorImage     = null;
    protected ?string $authorJobTitle  = null;

    // ── Organization ──────────────────────────────────────────────────────────

    protected ?string $orgName  = null;
    protected ?string $orgUrl   = null;
    protected ?string $orgLogo  = null;
    /** @var array<string> */
    protected array $orgSameAs = [];

    // ── Author setters ────────────────────────────────────────────────────────

    public function setAuthor(string $name, ?string $url = null): static
    {
        $this->authorName = $name;
        $this->authorUrl  = $url;
        return $this;
    }

    public function addAuthorSameAs(string $url): static
    {
        $this->authorSameAs[] = $url;
        return $this;
    }

    public function addAuthorCredential(string $credential): static
    {
        $this->authorCredentials[] = $credential;
        return $this;
    }

    public function setAuthorImage(string $url): static
    {
        $this->authorImage = $url;
        return $this;
    }

    public function setAuthorJobTitle(string $title): static
    {
        $this->authorJobTitle = $title;
        return $this;
    }

    // ── Organization setters ──────────────────────────────────────────────────

    public function setOrganization(string $name, string $url, ?string $logo = null): static
    {
        $this->orgName = $name;
        $this->orgUrl  = $url;
        $this->orgLogo = $logo;
        return $this;
    }

    public function addOrganizationSameAs(string $url): static
    {
        $this->orgSameAs[] = $url;
        return $this;
    }

    // ── Output ────────────────────────────────────────────────────────────────

    /**
     * Build the author Person schema array (for embedding in Article, etc.).
     *
     * @return array<string,mixed>
     */
    public function authorToArray(): array
    {
        $author = ['@type' => 'Person', 'name' => $this->authorName ?? ''];

        if ($this->authorUrl)       $author['url']      = $this->authorUrl;
        if ($this->authorImage)     $author['image']    = $this->authorImage;
        if ($this->authorJobTitle)  $author['jobTitle'] = $this->authorJobTitle;

        if (! empty($this->authorSameAs)) {
            $author['sameAs'] = count($this->authorSameAs) === 1
                ? $this->authorSameAs[0]
                : $this->authorSameAs;
        }

        if (! empty($this->authorCredentials)) {
            $author['hasCredential'] = array_map(
                fn($c) => ['@type' => 'EducationalOccupationalCredential', 'credentialCategory' => $c],
                $this->authorCredentials
            );
        }

        return $author;
    }

    /**
     * Build the Organization schema array (for embedding as publisher, etc.).
     *
     * @return array<string,mixed>
     */
    public function organizationToArray(): array
    {
        $org = [
            '@type' => 'Organization',
            'name'  => $this->orgName ?? '',
        ];

        if ($this->orgUrl)  $org['url']  = $this->orgUrl;
        if ($this->orgLogo) $org['logo'] = ['@type' => 'ImageObject', 'url' => $this->orgLogo];

        if (! empty($this->orgSameAs)) {
            $org['sameAs'] = count($this->orgSameAs) === 1
                ? $this->orgSameAs[0]
                : $this->orgSameAs;
        }

        return $org;
    }

    /**
     * Generate a standalone Author <script> block.
     */
    public function generateAuthorSchema(bool $minify = false): string
    {
        $data  = array_merge(['@context' => 'https://schema.org'], $this->authorToArray());
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR | ($minify ? 0 : JSON_PRETTY_PRINT);
        return '<script type="application/ld+json">' . json_encode($data, $flags) . '</script>';
    }

    /**
     * Generate a standalone Organization <script> block.
     */
    public function generateOrganizationSchema(bool $minify = false): string
    {
        $data  = array_merge(['@context' => 'https://schema.org'], $this->organizationToArray());
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR | ($minify ? 0 : JSON_PRETTY_PRINT);
        return '<script type="application/ld+json">' . json_encode($data, $flags) . '</script>';
    }
}
