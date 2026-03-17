<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class Recipe extends AbstractSchema
{
    protected function schemaType(): string
    {
        return 'Recipe';
    }

    protected function requiredFields(): array
    {
        return ['name', 'image', 'author', 'datePublished', 'description'];
    }

    public function setAuthor(string $author): static
    {
        return $this->set('author', ['@type' => 'Person', 'name' => $author]);
    }

    public function setPrepTime(string $duration): static
    {
        return $this->set('prepTime', $duration);
    }

    public function setCookTime(string $duration): static
    {
        return $this->set('cookTime', $duration);
    }

    public function setTotalTime(string $duration): static
    {
        return $this->set('totalTime', $duration);
    }

    public function setRecipeYield(string $yield): static
    {
        return $this->set('recipeYield', $yield);
    }

    /** @param array<string> $ingredients */
    public function setRecipeIngredient(array $ingredients): static
    {
        return $this->set('recipeIngredient', $ingredients);
    }

    /** @param array<array<string,string>> $instructions */
    public function setRecipeInstructions(array $instructions): static
    {
        return $this->set('recipeInstructions', $instructions);
    }

    /** @param array<string, mixed> $nutrition */
    public function setNutrition(array $nutrition): static
    {
        return $this->set('nutrition', \array_merge(['@type' => 'NutritionInformation'], $nutrition));
    }

    public function setAggregateRating(float $value, int $count): static
    {
        return $this->set('aggregateRating', [
            '@type'       => 'AggregateRating',
            'ratingValue' => $value,
            'ratingCount' => $count,
        ]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// VideoObject
// ─────────────────────────────────────────────────────────────────────────────
