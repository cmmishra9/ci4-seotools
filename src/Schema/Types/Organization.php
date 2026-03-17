<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class Organization extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'Organization';
    }

    public function setLogo(string $url): static
    {
        return $this->set('logo', ['@type' => 'ImageObject', 'url' => $url]);
    }

    public function setContactPoint(string $phone, string $type = 'customer service'): static
    {
        return $this->set('contactPoint', [
            '@type'       => 'ContactPoint',
            'telephone'   => $phone,
            'contactType' => $type,
        ]);
    }

    public function setFoundingDate(string $date): static
    {
        return $this->set('foundingDate', $date);
    }

    public function setNumberOfEmployees(int $count): static
    {
        return $this->set('numberOfEmployees', ['@type' => 'QuantitativeValue', 'value' => $count]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// LocalBusiness
// ─────────────────────────────────────────────────────────────────────────────
