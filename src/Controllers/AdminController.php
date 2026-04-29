<?php

declare(strict_types=1);

namespace Pubvana\Admin\Controllers;

use flight\Engine;

abstract class AdminController
{
    protected Engine $app;
    protected string $configPrepend;

    /**
     * @param Engine $app            The FlightPHP app instance
     * @param string $configPrepend  Config key prefix for this plugin's settings
     */
    public function __construct(Engine $app, string $configPrepend)
    {
        $this->app = $app;
        $this->configPrepend = $configPrepend;
    }

    /**
     * Render an admin view wrapped in the admin layout.
     *
     * @param string $view   View name relative to Views/ (e.g. 'dashboard/index')
     * @param array  $data   Data passed to the view
     * @param bool   $layout Wrap in admin layout — set false for HTMX partials
     */
    protected function render(string $view, array $data = [], bool $layout = true): void
    {
        if (!$layout) {
            $this->app->render($view, $data);
            return;
        }

        $content = $this->app->view()->fetch($view, $data);

        $user = $this->app->auth()->user();
        $userGroups = '';
        if ($user !== null) {
            $groups = $user->getGroups();
            $userGroups = implode(', ', $groups);
        }

        $config = $this->app->get($this->configPrepend) ?? [];

        $this->app->render('pubvana/admin/layouts/admin', [
            'content'    => $content,
            'pageTitle'  => $data['pageTitle'] ?? 'Dashboard',
            'siteName'   => $this->app->get('CMS.siteName') ?? 'Pubvana',
            'user'       => $user,
            'userGroups' => $userGroups,
            'menuSlots'  => [
                'content'    => $this->app->adext('menu', 'content'),
                'appearance' => $this->app->adext('menu', 'appearance'),
                'tools'      => $this->app->adext('menu', 'tools'),
                'settings'   => $this->app->adext('menu', 'settings'),
            ],
        ]);
    }
}
