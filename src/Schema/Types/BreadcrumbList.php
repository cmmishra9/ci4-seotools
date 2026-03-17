<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class BreadcrumbList extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'BreadcrumbList';
    }

    /** @var array<array<string,mixed>> */
    private array $items = [];

    public function reset(): static
    {
        $this->items = [];

        return parent::reset();
    }

    public function addItem(string $name, string $url, ?int $position = null): static
    {
        $position ??= (\count($this->items) + 1);
        $this->items[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => $name,
            'item'     => $url,
        ];

        return $this;
    }

    public function toArray(): array
    {
        $arr = parent::toArray();
        $arr['itemListElement'] = $this->items;

        return $arr;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// FAQPage
// ─────────────────────────────────────────────────────────────────────────────
