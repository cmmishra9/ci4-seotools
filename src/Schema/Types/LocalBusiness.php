<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class LocalBusiness extends AbstractSchema
{
    protected function schemaType(): string { return 'LocalBusiness'; }

    protected function requiredFields(): array
    {
        return ['name', 'address'];
    }

    /** @param array<string, mixed>|string $address */
    public function setAddress(array|string $address): static
    {
        if (is_string($address)) {
            $address = ['@type' => 'PostalAddress', 'streetAddress' => $address];
        }
        return $this->set('address', $address);
    }

    public function setGeo(float $lat, float $lng): static
    {
        return $this->set('geo', [
            '@type'     => 'GeoCoordinates',
            'latitude'  => $lat,
            'longitude' => $lng,
        ]);
    }

    /** @param string|array<string> $hours */
    public function setOpeningHours(string|array $hours): static
    {
        return $this->set('openingHours', $hours);
    }

    public function setPriceRange(string $range): static
    {
        return $this->set('priceRange', $range);
    }

    public function setTelephone(string $phone): static
    {
        return $this->set('telephone', $phone);
    }

    public function setCuisine(string $cuisine): static
    {
        return $this->set('servesCuisine', $cuisine);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// JobPosting
// ─────────────────────────────────────────────────────────────────────────────
