<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Enterprise;

/**
 * AiBotManager
 *
 * Centralises AI-bot access policies across multiple surfaces:
 *   1. robots.txt directives
 *   2. X-Robots-Tag HTTP headers (no-ai-training, noimageai, noarchive)
 *   3. robots meta tags (<meta name="robots">)
 *
 * Covers three distinct bot roles:
 *   - Retrieval bots  : crawl pages to answer user questions (GPTBot, ClaudeBot, Perplexity)
 *   - Training bots   : scrape content to train LLM datasets (CCBot, Bytespider)
 *   - Mixed bots      : both retrieval and training (Google-Extended)
 *
 * Usage:
 *   $mgr = new AiBotManager();
 *   $mgr->allowRetrieval()
 *       ->blockTraining()
 *       ->applyHeaders(service('response'));
 *
 *   // Or apply a named preset:
 *   $mgr->applyPreset('permissive');
 *
 * Presets:
 *   permissive  – allow retrieval + training
 *   retrieval   – allow retrieval, block training
 *   restrictive – block all AI bots
 */
class AiBotManager
{
    // All known AI-related crawlers, grouped by their primary role
    public const RETRIEVAL_BOTS = [
        'GPTBot', 'ClaudeBot', 'anthropic-ai', 'PerplexityBot',
        'GoogleOther', 'FacebookBot', 'Applebot-Extended',
    ];

    public const TRAINING_BOTS = [
        'CCBot', 'Bytespider', 'omgili', 'omgilibot', 'DataForSeoBot',
        'Diffbot', 'ImagesiftBot',
    ];

    public const MIXED_BOTS = [
        'Google-Extended',  // used for Bard/Gemini training
    ];

    protected bool $allowRetrieval = true;
    protected bool $allowTraining  = false;
    protected bool $allowImages    = true;

    /** @var array<string, 'allow'|'disallow'|array<string>> per-bot overrides */
    protected array $overrides = [];

    // ── Fluent policy setters ─────────────────────────────────────────────────

    public function allowRetrieval(bool $allow = true): static
    {
        $this->allowRetrieval = $allow;
        return $this;
    }

    public function blockRetrieval(): static
    {
        return $this->allowRetrieval(false);
    }

    public function allowTraining(bool $allow = true): static
    {
        $this->allowTraining = $allow;
        return $this;
    }

    public function blockTraining(): static
    {
        return $this->allowTraining(false);
    }

    public function allowImages(bool $allow = true): static
    {
        $this->allowImages = $allow;
        return $this;
    }

    /**
     * Override policy for a specific bot.
     *
     * @param 'allow'|'disallow'|array<string> $policy
     *   Pass 'allow', 'disallow', or an explicit array of robots.txt directives
     *   e.g. ['Allow: /public/', 'Disallow: /private/']
     */
    public function setBot(string $botName, string|array $policy): static
    {
        $this->overrides[$botName] = $policy;
        return $this;
    }

    // ── Presets ───────────────────────────────────────────────────────────────

    /**
     * Apply a named policy preset.
     *
     * @param 'permissive'|'retrieval'|'restrictive' $preset
     */
    public function applyPreset(string $preset): static
    {
        return match ($preset) {
            'permissive'  => $this->allowRetrieval()->allowTraining(),
            'retrieval'   => $this->allowRetrieval()->blockTraining(),
            'restrictive' => $this->blockRetrieval()->blockTraining(),
            default       => throw new \InvalidArgumentException("Unknown AiBotManager preset: {$preset}"),
        };
    }

    // ── HTTP header output ────────────────────────────────────────────────────

    /**
     * Apply X-Robots-Tag headers to a CI4 response.
     * These headers work even for non-HTML resources (PDFs, images).
     */
    public function applyHeaders(object $response): object
    {
        $directives = [];

        if (! $this->allowTraining) {
            $directives[] = 'noai';
            $directives[] = 'noimageai';
        }
        if (! $this->allowRetrieval) {
            $directives[] = 'noindex';
        }
        if (! $this->allowImages) {
            $directives[] = 'noimageindex';
        }

        if (! empty($directives) && method_exists($response, 'setHeader')) {
            $response->setHeader('X-Robots-Tag', implode(', ', $directives));
        }

        return $response;
    }

    // ── robots.txt rule set ───────────────────────────────────────────────────

    /**
     * Build robots.txt rules for every known AI bot.
     *
     * @return array<string, array<string>>
     *   Keys are bot names.
     *   Values are flat arrays of robots.txt directive lines, e.g.:
     *   ['Allow: /', 'Crawl-delay: 10']  or  ['Disallow: /']
     *
     * Feed the result into RobotsTxt or iterate it directly.
     */
    public function toRobotsTxtRules(): array
    {
        $rules   = [];
        $allBots = array_merge(self::RETRIEVAL_BOTS, self::TRAINING_BOTS, self::MIXED_BOTS);

        foreach ($allBots as $bot) {
            // Per-bot override takes precedence
            if (isset($this->overrides[$bot])) {
                $override = $this->overrides[$bot];
                if (is_array($override)) {
                    // Caller passed explicit directive lines
                    $rules[$bot] = $override;
                } else {
                    $rules[$bot] = $override === 'allow' ? ['Allow: /'] : ['Disallow: /'];
                }
                continue;
            }

            $isRetrieval = in_array($bot, self::RETRIEVAL_BOTS, true);
            $isTraining  = in_array($bot, self::TRAINING_BOTS, true);
            $isMixed     = in_array($bot, self::MIXED_BOTS, true);

            $shouldAllow = ($isRetrieval && $this->allowRetrieval)
                || ($isTraining  && $this->allowTraining)
                || ($isMixed     && ($this->allowRetrieval || $this->allowTraining));

            $rules[$bot] = $shouldAllow ? ['Allow: /'] : ['Disallow: /'];
        }

        return $rules;
    }

    // ── robots meta tag string ────────────────────────────────────────────────

    /**
     * Build a robots meta content value (e.g. "noai, noimageai").
     * Returns null if no AI-specific directives are needed.
     */
    public function toMetaContent(): ?string
    {
        $directives = [];

        if (! $this->allowTraining) {
            $directives[] = 'noai';
            $directives[] = 'noimageai';
        }
        if (! $this->allowRetrieval) {
            $directives[] = 'noindex';
            $directives[] = 'nofollow';
        }
        if (! $this->allowImages) {
            $directives[] = 'noimageindex';
        }

        return empty($directives) ? null : implode(', ', array_unique($directives));
    }
}
