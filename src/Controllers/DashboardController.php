<?php

declare(strict_types=1);

namespace Pubvana\Admin\Controllers;

/**
 * Admin dashboard — landing page after login.
 */
class DashboardController extends AdminController
{
    /**
     * Render the dashboard with cards and sections from adext contributors.
     *
     * @return void
     */
    public function index(): void
    {
        $user = $this->app->auth()->user();
        $context = [
            'user'      => $user,
            'site_name' => $this->app->settings()->get('CMS.siteName')
                ?? $this->app->get('CMS.siteName')
                ?? 'Pubvana',
        ];

        $cards = $this->normalizeEntries(
            $this->app->adext('page', 'dashboard.cards', $context),
            'label'
        );
        $sections = $this->normalizeEntries(
            $this->app->adext('page', 'dashboard.sections', $context),
            'title'
        );

        $this->render('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'username'  => $user->username ?? 'Admin',
            'cards'     => $cards,
            'sections'  => $sections,
        ]);
    }

    /**
     * Flatten adext contributions into a sorted entries array.
     *
     * @param array  $contributors Adext contributions keyed by source
     * @param string $requiredKey  Key that each entry must contain to be included
     * @return array
     */
    private function normalizeEntries(array $contributors, string $requiredKey): array
    {
        $entries = [];

        foreach ($contributors as $source => $contribution) {
            foreach ($contribution as $key => $entry) {
                if (!is_int($key) || !is_array($entry) || !isset($entry[$requiredKey])) {
                    continue;
                }

                $entry['source'] = $source;
                $entries[] = $this->normalizeDashboardUrls($entry);
            }
        }

        return $entries;
    }

    /**
     * Prepend /admin to relative URLs in a dashboard entry.
     *
     * @param array $entry Single dashboard card or section
     * @return array
     */
    private function normalizeDashboardUrls(array $entry): array
    {
        foreach (['href'] as $field) {
            if (!empty($entry[$field]) && is_string($entry[$field]) && str_starts_with($entry[$field], '/')) {
                $entry[$field] = '/admin' . $entry[$field];
            }
        }

        if (!empty($entry['items']) && is_array($entry['items'])) {
            foreach ($entry['items'] as $index => $item) {
                if (isset($item['href']) && is_string($item['href']) && str_starts_with($item['href'], '/')) {
                    $entry['items'][$index]['href'] = '/admin' . $item['href'];
                }
            }
        }

        return $entry;
    }
}
