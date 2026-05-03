<?php

declare(strict_types=1);

namespace Pubvana\Admin\Controllers;

use Pubvana\Blog\Controllers\BlogPublicController;
use Pubvana\Pages\Controllers\PagesPublicController;

/**
 * Dispatches the site homepage (/) based on CMS.frontPageRoute setting.
 *
 * The route value determines what renders:
 *   - blog prefix (default) → blog index rendered inline
 *   - pages prefix + slug   → static page rendered inline
 *   - anything else         → redirect to the custom route
 */
class HomepageController extends PublicController
{
    public function index(): void
    {
        $route = $this->getSetting('FrontPage.route') ?? '/blog';
        $blogPrefix = $this->app->pluginLoader()->routePrefix('pubvana/blog');
        $pagesPrefix = $this->app->pluginLoader()->routePrefix('pubvana/pages');

        // Blog index — render inline (no redirect)
        if ($route === $blogPrefix) {
            (new BlogPublicController($this->app, $this->configPrepend))
                ->index();
            return;
        }

        // Static page — render inline by slug
        if (str_starts_with($route, $pagesPrefix . '/')) {
            $slug = substr($route, strlen($pagesPrefix) + 1);
            if ($slug !== '') {
                (new PagesPublicController($this->app, $this->configPrepend))
                    ->show($slug);
                return;
            }
        }

        // Custom route — redirect so the owning plugin handles it
        $this->app->redirect($route);
    }
}
