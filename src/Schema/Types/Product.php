<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;
use RcsCodes\SEOTools\Schema\Types\Offer;

class Product extends AbstractSchema
{
    protected function schemaType(): string { return 'Product'; }

    protected function requiredFields(): array
    {
        return ['name', 'image', 'description'];
    }

    /** @param array<string, mixed>|Offer $offer */
    public function setOffers(array|Offer $offer): static
    {
        if ($offer instanceof Offer) {
            $offer = $offer->toEmbeddedArray();
        }
        return $this->set('offers', $offer);
    }

    /** @param array<string, mixed> $rating */
    public function setAggregateRating(array $rating): static
    {
        return $this->set('aggregateRating', $rating);
    }

    public function setSku(string $sku): static
    {
        return $this->set('sku', $sku);
    }

    public function setGtin(string $gtin): static
    {
        return $this->set('gtin', $gtin);
    }

    public function setBrand(string $brand): static
    {
        return $this->set('brand', ['@type' => 'Brand', 'name' => $brand]);
    }
}
