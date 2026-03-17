<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class JobPosting extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'JobPosting';
    }

    protected function requiredFields(): array
    {
        return ['title', 'description', 'hiringOrganization', 'datePosted', 'jobLocation'];
    }

    public function setTitle(string $title): static
    {
        return $this->set('title', $title);
    }

    public function setDatePosted(string $date): static
    {
        return $this->set('datePosted', $date);
    }

    public function setValidThrough(string $date): static
    {
        return $this->set('validThrough', $date);
    }

    public function setHiringOrganization(string $name, string $url, ?string $logo = null): static
    {
        $org = ['@type' => 'Organization', 'name' => $name, 'sameAs' => $url];

        if ($logo) {
            $org['logo'] = $logo;
        }

        return $this->set('hiringOrganization', $org);
    }

    public function setJobLocation(string $city, string $country, ?string $region = null): static
    {
        $addr = ['@type' => 'PostalAddress', 'addressLocality' => $city, 'addressCountry' => $country];

        if ($region) {
            $addr['addressRegion'] = $region;
        }

        return $this->set('jobLocation', ['@type' => 'Place', 'address' => $addr]);
    }

    public function setBaseSalary(float $min, float $max, string $currency, string $unitText = 'YEAR'): static
    {
        return $this->set('baseSalary', [
            '@type'    => 'MonetaryAmount',
            'currency' => $currency,
            'value'    => ['@type' => 'QuantitativeValue', 'minValue' => $min, 'maxValue' => $max, 'unitText' => $unitText],
        ]);
    }

    /** @param string|array<string> $type */
    public function setEmploymentType(string|array $type): static
    {
        return $this->set('employmentType', $type);
    }

    public function setRemote(bool $remote = true): static
    {
        return $this->set('jobLocationType', $remote ? 'TELECOMMUTE' : 'OFFICE');
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Recipe
// ─────────────────────────────────────────────────────────────────────────────
