<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Technical;

use RcsCodes\SEOTools\Concerns\HasDefaults;

/**
 * RobotsTxt
 *
 * Fluent generator for robots.txt with first-class AI-bot governance.
 *
 * Usage:
 *   $robots = new RobotsTxt();
 *   $robots->allow('*', '/')
 *          ->disallow('*', '/admin/')
 *          ->applyAiBotPresets()   // reads config ai_bots rules
 *          ->addSitemap('https://example.com/sitemap.xml');
 *   return $robots->toResponse();
 */
class RobotsTxt
{
    use HasDefaults;

    /**
     * @var array<string, array{allow: array<string>, disallow: array<string>, crawl_delay: int|null}>
     */
    protected array $rules = [];

    /** @var array<string> */
    protected array $sitemaps = [];

    /** Known AI crawlers and their dual-purpose directives */
    protected const AI_BOTS = [
        'GPTBot'          => ['retrieve' => true,  'noTrain' => false],
        'ClaudeBot'       => ['retrieve' => true,  'noTrain' => false],
        'PerplexityBot'   => ['retrieve' => true,  'noTrain' => false],
        'anthropic-ai'    => ['retrieve' => true,  'noTrain' => false],
        'Google-Extended' => ['retrieve' => true,  'noTrain' => false],
        'Diffbot'         => ['retrieve' => true,  'noTrain' => false],
        'CCBot'           => ['retrieve' => false, 'noTrain' => true],
        'Bytespider'      => ['retrieve' => false, 'noTrain' => true],
        'omgili'          => ['retrieve' => false, 'noTrain' => true],
        'omgilibot'       => ['retrieve' => false, 'noTrain' => true],
        'FacebookBot'     => ['retrieve' => true,  'noTrain' => false],
        'Applebot-Extended' => ['retrieve' => true, 'noTrain' => false],
    ];

    public function __construct()
    {
        $this->bootConfig();
        $this->applyConfigDefaults();
    }

    // -------------------------------------------------------------------------
    // Rule setters
    // -------------------------------------------------------------------------

    public function allow(string $userAgent, string $path): static
    {
        $this->ensureAgent($userAgent);
        $this->rules[$userAgent]['allow'][] = $path;

        return $this;
    }

    public function disallow(string $userAgent, string $path): static
    {
        $this->ensureAgent($userAgent);
        $this->rules[$userAgent]['disallow'][] = $path;

        return $this;
    }

    public function crawlDelay(string $userAgent, int $seconds): static
    {
        $this->ensureAgent($userAgent);
        $this->rules[$userAgent]['crawl_delay'] = $seconds;

        return $this;
    }

    public function addSitemap(string $url): static
    {
        $this->sitemaps[] = $url;

        return $this;
    }

    // -------------------------------------------------------------------------
    // AI bot governance
    // -------------------------------------------------------------------------

    /**
     * Apply the ai_bots rules from config.
     * Each bot can be: 'allow' | 'retrieve' | 'disallow'
     *
     *   allow    → Allow: /  (crawl AND train)
     *   retrieve → Allow: /  (crawl only; training blocked where supported via X-Robots-Tag)
     *   disallow → Disallow: /
     */
    public function applyAiBotPresets(): static
    {
        $presets = $this->config->robots['ai_bots'] ?? [];

        foreach ($presets as $bot => $rule) {
            match ($rule) {
                'allow'     => $this->allow($bot, '/'),
                'retrieve'  => $this->allow($bot, '/'),    // training blocked via HTTP header separately
                'disallow'  => $this->disallow($bot, '/'),
                default     => null,
            };
        }

        return $this;
    }

    /**
     * Block all known AI training crawlers in one call.
     */
    public function blockAllAiTraining(): static
    {
        foreach (self::AI_BOTS as $bot => $_) {
            $this->disallow($bot, '/');
        }

        return $this;
    }

    /**
     * Allow AI retrieval (for search/answers) but block training crawlers.
     */
    public function allowRetrievalBlockTraining(): static
    {
        foreach (self::AI_BOTS as $bot => $caps) {
            if ($caps['retrieve']) {
                $this->allow($bot, '/');
            } else {
                $this->disallow($bot, '/');
            }
        }

        return $this;
    }

    // -------------------------------------------------------------------------
    // Generator
    // -------------------------------------------------------------------------

    public function generate(): string
    {
        $lines = [];

        foreach ($this->rules as $agent => $rule) {
            $lines[] = 'User-agent: ' . $agent;

            foreach ($rule['allow'] as $path) {
                $lines[] = 'Allow: ' . $path;
            }

            foreach ($rule['disallow'] as $path) {
                $lines[] = 'Disallow: ' . $path;
            }

            if ($rule['crawl_delay'] !== null) {
                $lines[] = 'Crawl-delay: ' . $rule['crawl_delay'];
            }

            $lines[] = '';
        }

        foreach ($this->sitemaps as $sitemap) {
            $lines[] = 'Sitemap: ' . $sitemap;
        }

        return \implode("\n", $lines);
    }

    /**
     * Output as a CI4 Response with correct Content-Type.
     */
    public function toResponse(): \CodeIgniter\HTTP\ResponseInterface
    {
        /** @var \CodeIgniter\HTTP\ResponseInterface $response */
        $response = service('response');
        $response->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->setBody($this->generate());

        return $response;
    }

    public function reset(): static
    {
        $this->rules    = [];
        $this->sitemaps = [];
        $this->applyConfigDefaults();

        return $this;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function ensureAgent(string $agent): void
    {
        if (! isset($this->rules[$agent])) {
            $this->rules[$agent] = ['allow' => [], 'disallow' => [], 'crawl_delay' => null];
        }
    }

    protected function applyConfigDefaults(): void
    {
        foreach ($this->config->robots['default_rules'] ?? [] as $agent => $rule) {
            $this->ensureAgent($agent);

            foreach ($rule['allow']    ?? [] as $path) {
                $this->allow($agent, $path);
            }

            foreach ($rule['disallow'] ?? [] as $path) {
                $this->disallow($agent, $path);
            }
        }
    }
}
