<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Content;

use RcsCodes\SEOTools\Concerns\GeneratesHtml;

/**
 * AmpMeta
 *
 * Generates the paired <link> tags that connect AMP and canonical pages.
 *
 * On the **canonical** page:
 *   <link rel="amphtml" href="https://example.com/article?amp">
 *
 * On the **AMP** page:
 *   <link rel="canonical" href="https://example.com/article">
 *
 * Usage:
 *   // On canonical page:
 *   $amp = new AmpMeta();
 *   $amp->setAmpUrl('https://example.com/article?amp');
 *   echo $amp->generateForCanonical();
 *
 *   // On AMP page:
 *   $amp->setCanonicalUrl('https://example.com/article');
 *   echo $amp->generateForAmp();
 */
class AmpMeta
{
    use GeneratesHtml;

    protected ?string $ampUrl       = null;
    protected ?string $canonicalUrl = null;

    public function setAmpUrl(string $url): static
    {
        $this->ampUrl = $url;
        return $this;
    }

    public function setCanonicalUrl(string $url): static
    {
        $this->canonicalUrl = $url;
        return $this;
    }

    /**
     * Generate the amphtml link tag for use on the canonical page.
     * <link rel="amphtml" href="…">
     */
    public function generateForCanonical(): string
    {
        if ($this->ampUrl === null) {
            return '';
        }
        return $this->linkTag('amphtml', $this->ampUrl);
    }

    /**
     * Generate the canonical link tag for use on the AMP page.
     * <link rel="canonical" href="…">
     */
    public function generateForAmp(): string
    {
        if ($this->canonicalUrl === null) {
            return '';
        }
        return $this->linkTag('canonical', $this->canonicalUrl);
    }

    public function reset(): static
    {
        $this->ampUrl       = null;
        $this->canonicalUrl = null;
        return $this;
    }
}
