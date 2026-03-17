<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

/**
 * Review schema type.
 *
 * Required for Google rich results: itemReviewed, reviewRating, author.
 *
 * @see https://schema.org/Review
 */
class Review extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'Review';
    }

    protected function requiredFields(): array
    {
        return ['itemReviewed', 'reviewRating', 'author'];
    }

    /**
     * The item being reviewed — product, book, movie, software, business…
     *
     * @param array<string,mixed>|string $item  Pass a full schema array or a plain name string.
     */
    public function setItemReviewed(array|string $item): static
    {
        if (is_string($item)) {
            $item = ['@type' => 'Thing', 'name' => $item];
        }
        return $this->set('itemReviewed', $item);
    }

    /**
     * @param float  $value     Numeric rating e.g. 4.5
     * @param float  $bestRating  Maximum possible rating (default 5)
     * @param float  $worstRating Minimum possible rating (default 1)
     */
    public function setReviewRating(float $value, float $bestRating = 5.0, float $worstRating = 1.0): static
    {
        return $this->set('reviewRating', [
            '@type'       => 'Rating',
            'ratingValue' => $value,
            'bestRating'  => $bestRating,
            'worstRating' => $worstRating,
        ]);
    }

    /**
     * @param string|array<string,mixed> $author  Name or full Person/Organization array.
     */
    public function setAuthor(string|array $author): static
    {
        if (is_string($author)) {
            $author = ['@type' => 'Person', 'name' => $author];
        }
        return $this->set('author', $author);
    }

    public function setReviewBody(string $body): static
    {
        return $this->set('reviewBody', $body);
    }

    public function setDatePublished(string $date): static
    {
        return $this->set('datePublished', $date);
    }

    public function setPublisher(string $name, ?string $url = null): static
    {
        $publisher = ['@type' => 'Organization', 'name' => $name];
        if ($url !== null) {
            $publisher['url'] = $url;
        }
        return $this->set('publisher', $publisher);
    }
}
