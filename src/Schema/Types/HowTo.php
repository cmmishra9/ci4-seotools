<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class HowTo extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'HowTo';
    }

    protected function requiredFields(): array
    {
        return ['name', 'step'];
    }


    public function reset(): static
    {
        return parent::reset();
    }

    public function addStep(string $name, string $text, ?string $image = null): static
    {
        $step = ['@type' => 'HowToStep', 'name' => $name, 'text' => $text];

        if ($image !== null) {
            $step['image'] = $image;
        }

        return $this->append('step', $step);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Event
// ─────────────────────────────────────────────────────────────────────────────
