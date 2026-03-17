<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Concerns;

/**
 * Shared HTML tag-building helpers.
 * Used by SEOMeta, OpenGraph, TwitterCard, etc.
 */
trait GeneratesHtml
{
    /**
     * Build <meta name="..." content="...">
     */
    protected function metaNameTag(string $name, string $content): string
    {
        return '<meta name="' . esc($name) . '" content="' . esc($content) . '">';
    }

    /**
     * Build <meta property="..." content="...">
     */
    protected function metaPropertyTag(string $property, string $content): string
    {
        return '<meta property="' . esc($property) . '" content="' . esc($content) . '">';
    }

    /**
     * Build <meta {attr}="..." content="...">
     */
    protected function metaTag(string $attr, string $attrValue, string $content): string
    {
        return '<meta ' . esc($attr) . '="' . esc($attrValue) . '" content="' . esc($content) . '">';
    }

    /**
     * Build <link rel="..." href="..."> with optional extra attributes.
     *
     * @param array<string,string> $extras
     */
    protected function linkTag(string $rel, string $href, array $extras = []): string
    {
        $extra = '';

        foreach ($extras as $key => $val) {
            $extra .= ' ' . esc($key) . '="' . esc($val) . '"';
        }

        return '<link rel="' . esc($rel) . '" href="' . esc($href) . '"' . $extra . '>';
    }

    /**
     * Join lines with newline+indent for pretty output, or '' for minified.
     *
     * @param array<string> $lines
     */
    protected function joinLines(array $lines, bool $minify, string $indent = '    '): string
    {
        $lines = \array_values(\array_filter($lines));

        return \implode($minify ? '' : "\n" . $indent, $lines);
    }
}
