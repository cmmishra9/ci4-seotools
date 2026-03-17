<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class Event extends AbstractSchema
{
    protected function schemaType(): string { return 'Event'; }

    protected function requiredFields(): array
    {
        return ['name', 'startDate', 'location'];
    }

    public function setStartDate(string $date): static
    {
        return $this->set('startDate', $date);
    }

    public function setEndDate(string $date): static
    {
        return $this->set('endDate', $date);
    }

    /** @param string|array<string, mixed> $location */
    public function setLocation(string|array $location): static
    {
        if (is_string($location)) {
            $location = ['@type' => 'Place', 'name' => $location];
        }
        return $this->set('location', $location);
    }

    public function setOrganizer(string $name, ?string $url = null): static
    {
        $org = ['@type' => 'Organization', 'name' => $name];
        if ($url) $org['url'] = $url;
        return $this->set('organizer', $org);
    }

    /** @param array<string, mixed> $offers */
    public function setOffers(array $offers): static
    {
        return $this->set('offers', $offers);
    }

    public function setEventStatus(string $status): static
    {
        return $this->set('eventStatus', 'https://schema.org/' . ltrim($status, '/'));
    }

    public function setEventAttendanceMode(string $mode): static
    {
        return $this->set('eventAttendanceMode', 'https://schema.org/' . ltrim($mode, '/'));
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Organization
// ─────────────────────────────────────────────────────────────────────────────
