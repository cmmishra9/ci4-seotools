<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

/**
 * SoftwareApplication schema type.
 *
 * Required for Google rich results: name, operatingSystem, applicationCategory.
 *
 * @see https://schema.org/SoftwareApplication
 */
class SoftwareApplication extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'SoftwareApplication';
    }

    protected function requiredFields(): array
    {
        return ['name', 'operatingSystem', 'applicationCategory'];
    }

    /**
     * @param 'Windows'|'macOS'|'Linux'|'Android'|'iOS'|'Web'|string $os
     */
    public function setOperatingSystem(string $os): static
    {
        return $this->set('operatingSystem', $os);
    }

    /**
     * @param 'GameApplication'|'BusinessApplication'|'EducationalApplication'|'UtilitiesApplication'|string $category
     */
    public function setApplicationCategory(string $category): static
    {
        return $this->set('applicationCategory', $category);
    }

    public function setApplicationSubCategory(string $subCategory): static
    {
        return $this->set('applicationSubCategory', $subCategory);
    }

    public function setSoftwareVersion(string $version): static
    {
        return $this->set('softwareVersion', $version);
    }

    public function setDownloadUrl(string $url): static
    {
        return $this->set('downloadUrl', $url);
    }

    public function setInstallUrl(string $url): static
    {
        return $this->set('installUrl', $url);
    }

    public function setFileSizeBytes(int $bytes): static
    {
        return $this->set('fileSize', $bytes . ' bytes');
    }

    /**
     * @param 'Free'|'Paid'|string $price Use 'Free' or numeric string.
     */
    public function setOffers(string $price, string $currency = 'USD'): static
    {
        return $this->set('offers', [
            '@type'         => 'Offer',
            'price'         => $price === 'Free' ? '0' : $price,
            'priceCurrency' => $currency,
        ]);
    }

    public function setAggregateRating(float $value, int $count, float $best = 5.0): static
    {
        return $this->set('aggregateRating', [
            '@type'       => 'AggregateRating',
            'ratingValue' => $value,
            'ratingCount' => $count,
            'bestRating'  => $best,
        ]);
    }

    /** @param string|array<string> $url */
    public function setScreenshot(string|array $url): static
    {
        return $this->set('screenshot', $url);
    }

    public function setRequirements(string $requirements): static
    {
        return $this->set('softwareRequirements', $requirements);
    }

    public function setAuthor(string $name, ?string $url = null): static
    {
        $author = ['@type' => 'Organization', 'name' => $name];

        if ($url !== null) {
            $author['url'] = $url;
        }

        return $this->set('author', $author);
    }
}
