<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Technical;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RedirectHelper
 *
 * SEO-aware redirect management.
 * Warns about redirect chains in development, logs all redirects.
 *
 * Usage:
 *   RedirectHelper::register('/old-page', '/new-page');
 *   RedirectHelper::permanent('https://example.com/new-page');
 *   RedirectHelper::gone();   // 410 for deleted content
 */
class RedirectHelper
{
    /**
     * @var array<string, array{to: string|null, code: int}>
     */
    protected static array $map = [];

    /**
     * Register a redirect in the map.
     * Detects chains in development: warns when $to is itself already a source.
     */
    public static function register(string $from, ?string $to, int $code = 301): void
    {
        if (ENVIRONMENT !== 'production' && $to !== null && isset(static::$map[$to])) {
            log_message('warning', \sprintf(
                '[SEOTools] Redirect chain detected: %s → %s → %s. '
                . 'Collapse to a single redirect to preserve link equity.',
                $from,
                $to,
                static::$map[$to]['to'] ?? '(gone)',
            ));
        }

        static::$map[$from] = ['to' => $to, 'code' => $code];
    }

    /**
     * 301 Moved Permanently – passes full link equity.
     */
    public static function permanent(string $url): RedirectResponse
    {
        /** @var RedirectResponse $redirect */
        $redirect = redirect();

        return $redirect->to($url)->withCookies()->setStatusCode(301);
    }

    /**
     * 302 Found – temporary, no link equity transfer.
     */
    public static function temporary(string $url): RedirectResponse
    {
        /** @var RedirectResponse $redirect */
        $redirect = redirect();

        return $redirect->to($url)->withCookies()->setStatusCode(302);
    }

    /**
     * 410 Gone – tells search engines the content is permanently removed.
     * More SEO-positive than a 404 for intentionally deleted content.
     */
    public static function gone(?string $message = null): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = service('response');
        $response->setStatusCode(410);
        $response->setBody($message ?? 'This page has been permanently removed.');

        return $response;
    }

    /**
     * Check whether a source path has a registered redirect.
     */
    public static function has(string $from): bool
    {
        return isset(static::$map[$from]);
    }

    /**
     * Get the redirect record for a source path.
     *
     * @return array{to: string|null, code: int}|null
     */
    public static function get(string $from): ?array
    {
        return static::$map[$from] ?? null;
    }

    /**
     * @return array<string, array{to: string|null, code: int}>
     */
    public static function all(): array
    {
        return static::$map;
    }

    public static function flush(): void
    {
        static::$map = [];
    }
}
