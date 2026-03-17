<?php
/**
 * SEOTools — <head> partial
 *
 * Include this inside <head> to output all configured SEO tags.
 *
 * Usage in your layout:
 *   <?= view('seotools/meta') ?>
 *
 * Or with minification:
 *   <?= view('seotools/meta', ['minify' => true]) ?>
 */
$minify = $minify ?? false;
?>
    <?= seo_generate($minify) ?>
