<?php

namespace Clevyr\PageBuilder\app\Observers;

use Clevyr\PageBuilder\app\Models\Page;

/**
 * Class PageObserver
 * @package Clevyr\PageBuilder\app\Observers
 */
class PageObserver
{
    /**
     * Saving
     *
     * @param Page $page
     */
    public function saving(Page $page)
    {
        // Set attribute
        $page->setAttribute('published', $page->getIsPublishedAttribute());
    }
}
