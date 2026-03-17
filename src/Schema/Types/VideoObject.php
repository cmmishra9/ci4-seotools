<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Schema\Types;

use RcsCodes\SEOTools\Schema\AbstractSchema;

class VideoObject extends AbstractSchema
{
    protected function schemaType(): string { return 'VideoObject'; }

    protected function requiredFields(): array
    {
        return ['name', 'description', 'thumbnailUrl', 'uploadDate'];
    }

    /** @param string|array<string> $url */
    public function setThumbnailUrl(string|array $url): static
    {
        return $this->set('thumbnailUrl', $url);
    }

    public function setUploadDate(string $date): static
    {
        return $this->set('uploadDate', $date);
    }

    public function setDuration(string $duration): static
    {
        return $this->set('duration', $duration); // ISO 8601 e.g. PT4M30S
    }

    public function setContentUrl(string $url): static
    {
        return $this->set('contentUrl', $url);
    }

    public function setEmbedUrl(string $url): static
    {
        return $this->set('embedUrl', $url);
    }

    public function setInteractionCount(int $count): static
    {
        return $this->set('interactionStatistic', [
            '@type'                  => 'InteractionCounter',
            'interactionType'        => 'https://schema.org/WatchAction',
            'userInteractionCount'   => $count,
        ]);
    }
}
