<?php

namespace Roots\Acorn\Sage\Concerns;

trait FiltersThePost
{
    /**
     * Attach global `$post` variable to Blade views.
     *
     * Filter: the_post
     *
     * @param  WP_Post $post
     * @return void
     */
    public function filterThePost($post)
    {
        $this->view->share('post', $post);
    }
}
