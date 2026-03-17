<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class Article extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'Article';
    }

    protected function requiredFields(): array
    {
        return ['headline', 'author', 'datePublished'];
    }

    public function setHeadline(string $headline): static
    {
        return $this->set('headline', $headline);
    }

    /** @param string|array<string, mixed> $author */
    public function setAuthor(string|array $author): static
    {
        return $this->set('author', $author);
    }

    public function setDatePublished(string $date): static
    {
        return $this->set('datePublished', $date);
    }

    public function setDateModified(string $date): static
    {
        return $this->set('dateModified', $date);
    }

    /** @param string|array<string, mixed> $publisher */
    public function setPublisher(string|array $publisher): static
    {
        return $this->set('publisher', $publisher);
    }

    public function setArticleSection(string $section): static
    {
        return $this->set('articleSection', $section);
    }

    /** @param array<string>|string $keywords */
    public function setKeywords(array|string $keywords): static
    {
        return $this->set('keywords', \is_array($keywords) ? \implode(', ', $keywords) : $keywords);
    }

    public function setWordCount(int $count): static
    {
        return $this->set('wordCount', $count);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Product
// ─────────────────────────────────────────────────────────────────────────────
