<?php
/**
 * SEOTools — Schema @graph partial
 *
 * Outputs only the JSON-LD @graph block.
 * Useful when you want to place structured data at the bottom of <body>
 * rather than inside <head>.
 *
 * Usage:
 *   <?= view('seotools/schema') ?>
 */
$minify = $minify ?? false;

if (! seo_schema()->isEmpty()):
    echo seo_schema()->generate($minify);
endif;
