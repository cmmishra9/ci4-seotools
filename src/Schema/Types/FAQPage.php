<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class FAQPage extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'FAQPage';
    }

    /** @var array<array<string,mixed>> */
    private array $questions = [];

    public function reset(): static
    {
        $this->questions = [];

        return parent::reset();
    }

    public function addQuestion(string $question, string $answer): static
    {
        $this->questions[] = [
            '@type'          => 'Question',
            'name'           => $question,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $answer,
            ],
        ];

        return $this;
    }

    public function toArray(): array
    {
        $arr = parent::toArray();
        $arr['mainEntity'] = $this->questions;

        return $arr;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// HowTo
// ─────────────────────────────────────────────────────────────────────────────
