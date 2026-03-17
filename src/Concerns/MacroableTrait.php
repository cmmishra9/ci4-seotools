<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Concerns;

/**
 * Allows any class to accept runtime-defined methods (macros).
 *
 * Usage:
 *   SEOTools::macro('webPage', function(string $title, string $desc) { ... });
 *   seo()->webPage('Home', 'Welcome');
 */
trait MacroableTrait
{
    /** @var array<string, \Closure> */
    protected static array $macros = [];

    /**
     * Register a named macro.
     */
    public static function macro(string $name, \Closure $callback): void
    {
        static::$macros[$name] = $callback;
    }

    /**
     * Check if a macro with the given name exists.
     */
    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Remove a previously registered macro.
     */
    public static function flushMacros(): void
    {
        static::$macros = [];
    }

    /**
     * Dynamically call a registered macro.
     *
     * @param array<mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        if (isset(static::$macros[$method])) {
            $closure = \Closure::bind(static::$macros[$method], $this, static::class);

            return $closure(...$arguments);
        }

        throw new \BadMethodCallException(
            \sprintf('Method %s::%s() does not exist.', static::class, $method),
        );
    }

    /**
     * Statically call a registered macro.
     *
     * @param array<mixed> $arguments
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        if (isset(static::$macros[$method])) {
            $closure = \Closure::bind(static::$macros[$method], null, static::class);

            return $closure(...$arguments);
        }

        throw new \BadMethodCallException(
            \sprintf('Static method %s::%s() does not exist.', static::class, $method),
        );
    }
}
