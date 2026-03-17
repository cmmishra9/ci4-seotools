<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Concerns;

use RcsCodes\SEOTools\Config\SEOTools as SEOToolsConfig;

/**
 * Provides config bootstrapping for all SEOTools components.
 *
 * Production:  component calls bootConfig() in __construct() → reads config()
 * Testing:     pass a config instance directly to the constructor
 */
trait HasDefaults
{
    protected SEOToolsConfig $config;

    /**
     * Boot the config instance from CI4's service locator.
     * Call this in __construct() when no config is injected.
     */
    protected function bootConfig(?SEOToolsConfig $config = null): void
    {
        if ($config !== null) {
            $this->config = $config;
            return;
        }
        /** @var SEOToolsConfig $cfg */
        $cfg = config('SEOTools');
        $this->config = $cfg;
    }

    /**
     * Allow injecting a config after construction (useful in testing / multi-tenant).
     */
    public function setConfig(SEOToolsConfig $config): static
    {
        $this->config = $config;
        return $this;
    }
}
