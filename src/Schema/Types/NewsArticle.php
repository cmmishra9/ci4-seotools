<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

/**
 * NewsArticle schema type.
 *
 * Extends Article for news-specific rich results.
 * Required by Google News: headline, image, datePublished, author.
 *
 * @see https://schema.org/NewsArticle
 */
class NewsArticle extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'NewsArticle';
    }

    protected function requiredFields(): array
    {
        return ['headline', 'image', 'datePublished', 'author'];
    }

    public function setHeadline(string $headline): static
    {
        return $this->set('headline', $headline);
    }

    /**
     * @param string|array<string,mixed> $author  Name or Person array.
     */
    public function setAuthor(string|array $author): static
    {
        if (is_string($author)) {
            $author = ['@type' => 'Person', 'name' => $author];
        }
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

    /**
     * @param string|array<string,mixed> $publisher  Name or Organization/NewsMediaOrganization array.
     */
    public function setPublisher(string|array $publisher): static
    {
        if (is_string($publisher)) {
            $publisher = ['@type' => 'NewsMediaOrganization', 'name' => $publisher];
        }
        return $this->set('publisher', $publisher);
    }

    public function setArticleSection(string $section): static
    {
        return $this->set('articleSection', $section);
    }

    public function setArticleBody(string $body): static
    {
        return $this->set('articleBody', $body);
    }

    /**
     * Dateline — the city and optional date at the beginning of a news article.
     */
    public function setDateline(string $dateline): static
    {
        return $this->set('dateline', $dateline);
    }

    public function setWordCount(int $count): static
    {
        return $this->set('wordCount', $count);
    }

    /** @param string|array<string> $keywords */
    public function setKeywords(string|array $keywords): static
    {
        return $this->set(
            'keywords',
            is_array($keywords) ? implode(', ', $keywords) : $keywords
        );
    }
}
