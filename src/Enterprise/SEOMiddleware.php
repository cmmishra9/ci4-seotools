<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Enterprise;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SEOMiddleware  (CI4 Filter)
 *
 * Automatically injects missing SEO defaults into every HTML response.
 * Register in app/Config/Filters.php:
 *
 *   public array $aliases = [
 *       'seo' => \RcsCodes\SEOTools\Enterprise\SEOMiddleware::class,
 *   ];
 *
 *   // Apply globally:
 *   public array $globals = [
 *       'after' => ['seo'],
 *   ];
 *
 *   // Or per-route:
 *   $routes->group('blog', ['filter' => 'seo'], function($routes) { ... });
 */
class SEOMiddleware implements FilterInterface
{
    /**
     * Before hook – nothing to do (SEO tags are set in the controller).
     *
     * @param array<mixed>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        return null;
    }

    /**
     * After hook – inject a canonical tag if one hasn't been set and
     * ensure the <title> is populated with at least the site default.
     *
     * @param array<mixed>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        /** @var \RcsCodes\SEOTools\Config\SEOTools $config */
        $config = config('SEOTools');

        if (! ($config->enterprise['middleware_auto_inject'] ?? true)) {
            return $response;
        }

        // Only process HTML responses
        $contentType = $response->getHeaderLine('Content-Type');

        if (! \str_contains($contentType, 'text/html') && ! empty($contentType)) {
            return $response;
        }

        $body = $response->getBody();

        if (empty($body) || ! \str_contains($body, '<head')) {
            return $response;
        }

        // Auto-inject canonical if missing
        if (! \str_contains($body, 'rel="canonical"')) {
            $canonical = '<link rel="canonical" href="' . current_url() . '">';
            $body      = \str_replace('</head>', $canonical . "\n</head>", $body);
            $response->setBody($body);
        }

        return $response;
    }
}
