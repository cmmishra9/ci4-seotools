<?php

declare(strict_types=1);

namespace RcsCodes\SEOTools\Traits;

use RcsCodes\SEOTools\SEOTools;

/**
 * SEOToolsTrait
 *
 * Add to any CI4 Controller to access the SEOTools instance via $this->seo().
 *
 * Usage:
 *   class BlogController extends BaseController
 *   {
 *       use \RcsCodes\SEOTools\Traits\SEOToolsTrait;
 *
 *       public function show(int $id): string
 *       {
 *           $post = $this->postModel->find($id);
 *           $this->seo()->setTitle($post->title);
 *           $this->seo()->setDescription($post->excerpt);
 *           $this->seo()->opengraph()->addImage($post->cover_url);
 *           return view('blog/show', ['post' => $post]);
 *       }
 *   }
 */
trait SEOToolsTrait
{
    private ?SEOTools $_seotools = null;

    /**
     * Get (or lazy-initialise) the per-controller SEOTools instance.
     */
    public function seo(): SEOTools
    {
        if ($this->_seotools === null) {
            $this->_seotools = new SEOTools();
        }
        return $this->_seotools;
    }
}
