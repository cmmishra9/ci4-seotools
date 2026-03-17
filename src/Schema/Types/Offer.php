<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class Offer extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'Offer';
    }

    public function setPrice(float|string $price): static
    {
        return $this->set('price', $price);
    }

    public function setPriceCurrency(string $currency): static
    {
        return $this->set('priceCurrency', $currency);
    }

    public function setAvailability(string $availability): static
    {
        return $this->set('availability', 'https://schema.org/' . \ltrim($availability, '/'));
    }

    public function setPriceValidUntil(string $date): static
    {
        return $this->set('priceValidUntil', $date);
    }

    public function setSeller(string $seller): static
    {
        return $this->set('seller', ['@type' => 'Organization', 'name' => $seller]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// BreadcrumbList
// ─────────────────────────────────────────────────────────────────────────────
