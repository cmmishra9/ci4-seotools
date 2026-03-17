<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap for ci4-seotools.
 */

namespace CodeIgniter\Config {
    class BaseConfig {
        public function __construct() {}
    }
}

namespace CodeIgniter\HTTP {
    interface ResponseInterface {
        public function setHeader(string $name, $value): object;
        public function setContentType(string $mimeType, string $charset = 'UTF-8'): object;
        public function setBody($content): object;
        public function setStatusCode(int $code, string $reason = ''): object;
        public function getBody();
        public function getStatusCode(): int;
    }
    interface RequestInterface {
        public function getMethod(bool $upper = false): string;
    }
}

namespace Config {
    class Modules extends \CodeIgniter\Config\BaseConfig {
        public $enabled = true;
        public $discoverInVendors = true;
        public $aliases = [];
    }
    class Autoload extends \CodeIgniter\Config\BaseConfig {
        public $psr4 = [];
        public $classmap = [];
    }
}

namespace RcsCodes\SEOTools\Config {
    class SEOTools {
        public array $meta = [
            'defaults' => [
                'title'        => false,
                'titleBefore'  => true,
                'description'  => false,
                'separator'    => ' | ',
                'keywords'     => [],
                'canonical'    => false,
                'robots'       => false,
            ],
            'webmaster_tags' => [
                'google'    => null,
                'bing'      => null,
                'alexa'     => null,
                'pinterest' => null,
                'yandex'    => null,
                'norton'    => null,
                'norton'    => null,
                'yandex'    => null,
                'pinterest' => null,
                'alexa'     => null,
                'bing'      => null,
                'google'    => null,
            ],
        ];
        public array $opengraph = [
            'defaults' => [
                'title'       => false,
                'description' => false,
                'url'         => null,
                'type'        => false,
                'site_name'   => false,
                'locale'      => false,
                'images'      => [],
            ],
        ];
        public array $twitter = [
            'defaults' => [
                'card'        => 'summary_large_image',
                'site'        => false,
                'creator'     => false,
                'title'       => false,
                'description' => false,
                'image'       => false,
            ],
        ];
        public array $jsonld = [
            'defaults' => [
                'type'        => 'WebPage',
                'title'       => false,
                'description' => false,
                'url'         => null,
                'images'      => [],
            ],
        ];
        public array $sitemap = [
            'cache'         => true,
            'cache_duration'=> 3600,
            'gzip'          => false,
            'max_urls'      => 50000,
            'defaults' => [
                'changefreq' => 'weekly',
                'priority'   => '0.5',
            ],
        ];
        public array $robots = [
            'default_rules' => [
                '*' => [
                    'allow'     => ['/'],
                    'disallow'  => ['/admin/', '/private/'],
                ],
            ],
            'ai_bots' => [
                'GPTBot'          => 'retrieve',
                'ClaudeBot'       => 'retrieve',
                'PerplexityBot'   => 'retrieve',
                'CCBot'           => 'disallow',
                'Bytespider'      => 'disallow',
                'Diffbot'         => 'retrieve',
                'anthropic-ai'    => 'retrieve',
                'Google-Extended' => 'retrieve',
            ],
        ];
        public array $tenants = [];
        public array $enterprise = [
            'middleware_auto_inject' => true,
            'schema_validation'      => true,
            'debug_mode'             => false,
        ];
    }
}

namespace {
    define('HOMEPATH', __DIR__ . '/../');
    define('CONFIGPATH', __DIR__ . '/../');
    define('APPPATH', __DIR__ . '/../');
    define('FCPATH', __DIR__ . '/../');

    require_once __DIR__ . '/../vendor/autoload.php';

    if (! defined('ENVIRONMENT')) {
        define('ENVIRONMENT', 'testing');
    }

    class TestConfig
    {
        private static ?object $instance = null;

        public static function get(): object
        {
            if (self::$instance === null) {
                self::$instance = new \RcsCodes\SEOTools\Config\SEOTools();
            }
            return self::$instance;
        }

        public static function reset(): void
        {
            self::$instance = null;
        }

        public static function merge(array $data): void
        {
            $instance = self::get();
            foreach ($data as $key => $value) {
                if (isset($instance->{$key}) && is_array($instance->{$key}) && is_array($value)) {
                    $instance->{$key} = array_replace_recursive($instance->{$key}, $value);
                } else {
                    $instance->{$key} = $value;
                }
            }
        }
    }

    if (! function_exists('config')) {
        function config(string $name): object { 
            return \TestConfig::get();
        }
    }

    if (! function_exists('current_url')) {
        function current_url(): string { return $_SERVER['TEST_CURRENT_URL'] ?? 'https://example.com/current-page'; }
    }

    if (! function_exists('esc')) {
        function esc(mixed $data, string $context = 'html'): string {
            return htmlspecialchars((string) $data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }

    if (! function_exists('log_message')) {
        function log_message(string $level, string $message): void {}
    }

    if (! function_exists('redirect')) {
        function redirect(string $uri = ''): object {
            return new class {
                public function to(string $uri): object { return $this; }
                public function with(string $key, mixed $value): object { return $this; }
            };
        }
    }

    if (! function_exists('service')) {
        function service(string $name): object {
            return new class implements \CodeIgniter\HTTP\ResponseInterface {
                private string $body = '';
                private array $headers = [];
                private int $statusCode = 200;
                public function setHeader(string $k, $v): object { $this->headers[$k] = $v; return $this; }
                public function getHeaderLine(string $k): string { return $this->headers[$k] ?? ''; }
                public function setContentType(string $m, string $c = 'UTF-8'): object { return $this; }
                public function setBody($body): object { $this->body = (string)$body; return $this; }
                public function getBody(): string { return $this->body; }
                public function setStatusCode(int $code, string $reason = ''): object { $this->statusCode = $code; return $this; }
                public function getStatusCode(): int { return $this->statusCode; }
            };
        }
    }
}
