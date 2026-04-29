<?php

declare(strict_types=1);

namespace Pubvana\Admin\Controllers;

class SettingsController extends AdminController
{
    public function index(?string $class = null): void
    {
        $settings = $this->app->settings();
        $classes = $settings->getClasses();

        if ($class === null && !empty($classes)) {
            $class = $classes[0];
        }

        $fields = $class !== null ? $settings->getClass($class) : [];

        $this->render('settings/index', [
            'pageTitle'   => 'Settings',
            'classes'     => $classes,
            'activeClass' => $class,
            'fields'      => $fields,
        ]);
    }

    public function save(string $class): void
    {
        $post = $this->app->request()->data->getData();
        unset($post['_csrf_token']);

        $this->app->settings()->saveClass($class, $post);

        $this->app->redirect("/admin/settings/{$class}");
    }
}
