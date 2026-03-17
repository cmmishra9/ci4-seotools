<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

/**
 * Course schema type.
 *
 * Required for Google rich results: name, description, provider.
 *
 * @see https://schema.org/Course
 */
class Course extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'Course';
    }

    protected function requiredFields(): array
    {
        return ['name', 'description', 'provider'];
    }

    /**
     * The organisation offering this course.
     */
    public function setProvider(string $name, ?string $url = null): static
    {
        $provider = ['@type' => 'Organization', 'name' => $name];
        if ($url !== null) {
            $provider['sameAs'] = $url;
        }
        return $this->set('provider', $provider);
    }

    /**
     * ISO 8601 duration e.g. PT2H30M (2 hours 30 minutes).
     */
    public function setTimeRequired(string $duration): static
    {
        return $this->set('timeRequired', $duration);
    }

    /**
     * @param 'Beginner'|'Intermediate'|'Advanced'|string $level
     */
    public function setEducationalLevel(string $level): static
    {
        return $this->set('educationalLevel', $level);
    }

    /**
     * Programming language or subject this course teaches.
     */
    public function setAbout(string $topic): static
    {
        return $this->set('about', ['@type' => 'Thing', 'name' => $topic]);
    }

    public function setInLanguage(string $languageCode): static
    {
        return $this->set('inLanguage', $languageCode);
    }

    /**
     * @param 'Free'|'Paid'|string $price  Use 'Free' or a numeric string
     */
    public function setCoursePrice(string $price, string $currency = 'USD'): static
    {
        if ($price === 'Free' || (float) $price === 0.0) {
            return $this->set('offers', [
                '@type'         => 'Offer',
                'price'         => '0',
                'priceCurrency' => $currency,
                'category'      => 'Free',
            ]);
        }
        return $this->set('offers', [
            '@type'         => 'Offer',
            'price'         => $price,
            'priceCurrency' => $currency,
        ]);
    }

    public function setCourseMode(string $mode): static
    {
        return $this->set('courseMode', $mode);
    }

    public function setAggregateRating(float $value, int $count): static
    {
        return $this->set('aggregateRating', [
            '@type'       => 'AggregateRating',
            'ratingValue' => $value,
            'ratingCount' => $count,
        ]);
    }
}
