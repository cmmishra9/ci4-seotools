<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Meta;

use RcsCodes\SEOTools\Concerns\GeneratesHtml;
use RcsCodes\SEOTools\Concerns\HasDefaults;
use RcsCodes\SEOTools\Contracts\OpenGraphInterface;

/**
 * OpenGraph
 *
 * Generates all og:* meta property tags including all
 * namespace-specific helpers for article, book, profile,
 * music, video, and place.
 *
 * @see https://ogp.me/
 */
class OpenGraph implements OpenGraphInterface
{
    use HasDefaults;
    use GeneratesHtml;

    /** @var array<string,mixed> */
    protected array $properties = [];

    /** @var array<array<string,string>> */
    protected array $images = [];

    /** @var array<array<string,string>> */
    protected array $videos = [];

    /** @var array<array<string,string>> */
    protected array $audios = [];

    public function __construct()
    {
        $this->bootConfig();
        $this->applyDefaults();
    }

    // -------------------------------------------------------------------------
    // Core property setters
    // -------------------------------------------------------------------------

    /** @param string|array<string> $value */
    public function addProperty(string $key, array|string $value): static
    {
        // Guard: strip leading 'og:' prefix — we add it ourselves in generate()
        if (str_starts_with($key, 'og:')) {
            $key = substr($key, 3);
        }
        $this->properties[$key] = $value;
        return $this;
    }

    public function setTitle(string $title): static
    {
        return $this->addProperty('title', $title);
    }

    public function setDescription(string $description): static
    {
        return $this->addProperty('description', strip_tags($description));
    }

    public function setUrl(string $url): static
    {
        return $this->addProperty('url', $url);
    }

    public function setType(string $type): static
    {
        return $this->addProperty('type', $type);
    }

    public function setSiteName(string $name): static
    {
        return $this->addProperty('site_name', $name);
    }

    public function setLocale(string $locale): static
    {
        return $this->addProperty('locale', $locale);
    }

    // -------------------------------------------------------------------------
    // Media setters
    // -------------------------------------------------------------------------

    /**
     * @param string|array<string,string> $url
     * @param array<string, string> $attrs
     */
    public function addImage(array|string $url, array $attrs = []): static
    {
        if (is_array($url)) {
            $this->images[] = $url;
        } elseif (! empty($attrs)) {
            $this->images[] = array_merge(['url' => $url], $attrs);
        } else {
            $this->images[] = ['url' => $url];
        }
        return $this;
    }

    /** @param array<string|array<string,string>> $urls */
    public function addImages(array $urls): static
    {
        foreach ($urls as $url) {
            $this->addImage($url);
        }
        return $this;
    }

    /** @param array<string, string> $attrs */
    public function addVideo(string $url, array $attrs = []): static
    {
        $this->videos[] = array_merge(['url' => $url], $attrs);
        return $this;
    }

    /** @param array<string, string> $attrs */
    public function addAudio(string $url, array $attrs = []): static
    {
        $this->audios[] = array_merge(['url' => $url], $attrs);
        return $this;
    }

    // -------------------------------------------------------------------------
    // Namespace-specific helpers (article, book, profile, music, video, place)
    // -------------------------------------------------------------------------

    /** @param array<string,mixed> $attributes */
    public function setArticle(array $attributes): static
    {
        return $this->setNamespace('article', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setBook(array $attributes): static
    {
        return $this->setNamespace('book', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setProfile(array $attributes): static
    {
        return $this->setNamespace('profile', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setMusicSong(array $attributes): static
    {
        return $this->setNamespace('music', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setMusicAlbum(array $attributes): static
    {
        return $this->setNamespace('music', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setMusicPlaylist(array $attributes): static
    {
        return $this->setNamespace('music', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setMusicRadioStation(array $attributes): static
    {
        return $this->setNamespace('music', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setVideoMovie(array $attributes): static
    {
        return $this->setNamespace('video', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setVideoEpisode(array $attributes): static
    {
        return $this->setNamespace('video', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setVideoTVShow(array $attributes): static
    {
        return $this->setNamespace('video', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setVideoOther(array $attributes): static
    {
        return $this->setNamespace('video', $attributes);
    }

    /** @param array<string,mixed> $attributes */
    public function setPlace(array $attributes): static
    {
        return $this->setNamespace('place', $attributes);
    }

    // -------------------------------------------------------------------------
    // Generator
    // -------------------------------------------------------------------------

    public function generate(bool $minify = false): string
    {
        $html = [];

        foreach ($this->properties as $key => $value) {
            if ($key === 'url' && $value === '__auto__') {
                $value = current_url();
            }
            if (is_array($value)) {
                foreach ($value as $val) {
                    $html[] = $this->metaPropertyTag('og:' . $key, (string) $val);
                }
            } else {
                $html[] = $this->metaPropertyTag('og:' . $key, (string) $value);
            }
        }

        foreach ($this->images as $image) {
            $url    = is_array($image) ? ($image['url'] ?? '') : $image;
            $html[] = $this->metaPropertyTag('og:image', $url);

            if (is_array($image)) {
                // Auto-populate secure_url when image is https and secure_url not set
                if (empty($image['secure_url']) && str_starts_with($url, 'https://')) {
                    $image['secure_url'] = $url;
                }
                foreach (['secure_url', 'type', 'width', 'height', 'size', 'alt'] as $attr) {
                    if (! empty($image[$attr])) {
                        $html[] = $this->metaPropertyTag('og:image:' . $attr, (string) $image[$attr]);
                    }
                }
            }
        }

        foreach ($this->videos as $video) {
            $html[] = $this->metaPropertyTag('og:video', $video['url']);
            foreach (['secure_url', 'type', 'width', 'height'] as $attr) {
                if (! empty($video[$attr])) {
                    $html[] = $this->metaPropertyTag('og:video:' . $attr, $video[$attr]);
                }
            }
        }

        foreach ($this->audios as $audio) {
            $html[] = $this->metaPropertyTag('og:audio', $audio['url']);
            foreach (['secure_url', 'type'] as $attr) {
                if (! empty($audio[$attr])) {
                    $html[] = $this->metaPropertyTag('og:audio:' . $attr, $audio[$attr]);
                }
            }
        }

        return $this->joinLines($html, $minify);
    }

    // -------------------------------------------------------------------------
    // Reset
    // -------------------------------------------------------------------------

    public function reset(): static
    {
        $this->properties = [];
        $this->images     = [];
        $this->videos     = [];
        $this->audios     = [];
        $this->applyDefaults();
        return $this;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $attributes */
    protected function setNamespace(string $ns, array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $full = str_starts_with($key, $ns . ':') ? $key : $ns . ':' . $key;
            $this->properties[$full] = $value;
        }
        return $this;
    }

    protected function applyDefaults(): void
    {
        $d = $this->config->opengraph['defaults'] ?? [];

        foreach ($d as $key => $value) {
            if ($value === false) {
                continue;
            }
            if ($key === 'images') {
                foreach ((array) $value as $img) {
                    $this->addImage($img);
                }
                continue;
            }
            if ($key === 'url' && $value === null) {
                $value = '__auto__'; // resolved lazily in generate()
            }
            if ($value !== false && $value !== null) {
                $this->properties[$key] = $value;
            }
        }
    }
}
