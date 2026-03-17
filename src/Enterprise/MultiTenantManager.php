<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Enterprise;

use RcsCodes\SEOTools\Concerns\HasDefaults;

/**
 * MultiTenantManager
 *
 * Applies per-domain SEO configuration overrides at runtime.
 * Useful for SaaS platforms serving multiple brands from one codebase.
 *
 * Config (app/Config/SEOTools.php):
 *
 *   public array $tenants = [
 *       'brand-a.example.com' => [
 *           'meta' => ['defaults' => ['title' => 'Brand A']],
 *           'opengraph' => ['defaults' => ['site_name' => 'Brand A']],
 *       ],
 *       'brand-b.example.com' => [
 *           'meta' => ['defaults' => ['title' => 'Brand B', 'separator' => ' | ']],
 *       ],
 *   ];
 *
 * Usage:
 *   $manager = new MultiTenantManager();
 *   $manager->apply();   // call once in BaseController::initController()
 */
class MultiTenantManager
{
    use HasDefaults;

    public function __construct()
    {
        $this->bootConfig();
    }

    /**
     * Detect the current domain and merge its overrides into the live config.
     *
     * @param  object|null $config  If supplied, merges into this config object and returns it.
     *                              If null, merges into the instance config in-place.
     * @return object  The (possibly mutated) config object.
     */
    public function apply(?object $config = null): object
    {
        if ($config !== null) {
            /** @var \RcsCodes\SEOTools\Config\SEOTools $config */
            $this->config = $config;
        }

        $domain   = $this->currentDomain();
        $tenants  = $this->config->tenants ?? [];

        // Exact match first, then wildcard prefix match
        $override = $tenants[$domain] ?? $this->wildcardMatch($domain, $tenants);

        if (! empty($override)) {
            $this->mergeConfig($override);
        }

        return $this->config;
    }

    /**
     * Manually apply an override array (useful in tests or non-HTTP contexts).
     *
     * @param array<string,mixed> $override
     */
    public function applyArray(array $override): void
    {
        $this->mergeConfig($override);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function currentDomain(): string
    {
        // Prefer HTTP_HOST (set by web server and testable without a full URL parse)
        if (! empty($_SERVER['HTTP_HOST'])) {
            return strtolower($_SERVER['HTTP_HOST']);
        }
        $host = parse_url(current_url(), PHP_URL_HOST);
        return is_string($host) ? $host : '';
    }

    /**
     * @param array<string,mixed> $tenants
     * @return array<string,mixed>
     */
    protected function wildcardMatch(string $domain, array $tenants): array
    {
        foreach ($tenants as $pattern => $config) {
            if (str_starts_with($pattern, '*.')) {
                $suffix = substr($pattern, 2);
                if (str_ends_with($domain, $suffix)) {
                    return $config;
                }
            }
        }
        return [];
    }

    /**
     * Deep-merge an override array into the live config object.
     *
     * @param array<string,mixed> $override
     */
    protected function mergeConfig(array $override): void
    {
        foreach ($override as $section => $values) {
            if (property_exists($this->config, $section) && is_array($values)) {
                /** @var array<string,mixed> $existing */
                $existing = $this->config->{$section};
                $this->config->{$section} = array_replace_recursive($existing, $values);
            }
        }
    }
}
