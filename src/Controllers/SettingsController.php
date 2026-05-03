<?php

declare(strict_types=1);

namespace Pubvana\Admin\Controllers;

/**
 * Admin settings editor — view and save database-backed settings by class.
 */
class SettingsController extends AdminController
{
    /**
     * Display settings for a class, defaulting to the first available.
     *
     * @param string|null $class Settings class name, or null for the first one
     * @return void
     */
    public function index(?string $class = null): void
    {
        $settings = $this->app->settings();
        $classes = $settings->getClasses();

        if ($class === null && !empty($classes)) {
            $class = $classes[0];
        }

        $fields = $class !== null ? $settings->getClass($class) : [];

        $data = [
            'pageTitle'   => 'Settings',
            'classes'     => $classes,
            'activeClass' => $class,
            'fields'      => $fields,
        ];

        if ($class === 'FrontPage') {
            $data['pages'] = $this->app->pages()->listPublished();
        }

        $this->render('settings/index', $data);
    }

    /**
     * Save the front page type and route setting.
     *
     * @return void
     */
    public function saveFrontPage(): void
    {
        $post = $this->app->request()->data->getData();
        $type = $post['front_page_type'] ?? 'blog';

        $route = match ($type) {
            'page'   => $this->app->pluginLoader()->routePrefix('pubvana/pages') . '/' . ($post['front_page_slug'] ?? ''),
            'custom' => $post['front_page_custom'] ?? '/',
            default  => $this->app->pluginLoader()->routePrefix('pubvana/blog'),
        };

        $this->app->settings()->set('FrontPage.route', $route);

        $this->app->redirect('/admin/settings/FrontPage');
    }

    /**
     * Save all posted key/value pairs for a settings class.
     *
     * @param string $class Settings class name
     * @return void
     */
    public function save(string $class): void
    {
        $post = $this->app->request()->data->getData();
        unset($post['_csrf_token']);

        $this->app->settings()->saveClass($class, $post);

        $this->app->redirect("/admin/settings/{$class}");
    }
}
