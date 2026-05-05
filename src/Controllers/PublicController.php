<?php

declare(strict_types=1);

namespace Pubvana\Admin\Controllers;

use Enlivenapp\FlightSchool\Exception\ConfigurationException;
use Enlivenapp\FlightSchool\Exception\NotFoundException;
use Enlivenapp\FlightSchool\PluginView;
use flight\Engine;

/**
 * Base controller for public-facing pages.
 *
 * Assembles global data (site, head, nav, regions), merges route-specific
 * data from child controllers, and renders through the active theme's
 * Vision templates.
 */
abstract class PublicController
{
    protected Engine $app;
    protected string $configPrepend;

    public function __construct(Engine $app, string $configPrepend)
    {
        $this->app = $app;
        $this->configPrepend = $configPrepend;
    }

    /**
     * Render a theme template with global + route-specific data.
     *
     * @param string $template Template name relative to theme's Views/ (e.g. 'post', 'home')
     * @param array  $data     Route-specific data from the child controller
     */
    protected function render(string $template, array $data = []): void
    {
        $view = $this->app->view();

        if (!($view instanceof PluginView)) {
            throw new ConfigurationException('PublicController requires PluginView (flight-school).');
        }

        $themePath = $view->getThemePath();

        if ($themePath === null) {
            throw new ConfigurationException('No active theme configured.');
        }

        $global = $this->buildGlobalData($data);
        $viewData = array_merge($global, $this->processRenderableContent($data));

        $siteName = $this->getSiteName();
        $pageTitle = $data['title'] ?? $data['archive_title'] ?? 'Home';
        $viewData['header']['title'] = $pageTitle . ' - ' . $siteName;

        // Resolve template: app/views/themes/{name}/ override → theme's Views/
        $appOverride = $this->app->get('flight.views.path')
            . DIRECTORY_SEPARATOR . 'themes'
            . DIRECTORY_SEPARATOR . $this->getActiveThemeName()
            . DIRECTORY_SEPARATOR . $template . '.tpl';

        if (file_exists($appOverride)) {
            $templateFile = $appOverride;
        } else {
            $templateFile = $themePath . DIRECTORY_SEPARATOR . $template . '.tpl';
        }

        if (!file_exists($templateFile)) {
            throw new NotFoundException("Theme template not found: {$template}.tpl");
        }

        // Render through Vision with theme's Views/ as basePath for extends/includes
        $engine = $view->vision();
        $basePath = $themePath . DIRECTORY_SEPARATOR;
        echo $engine->render($templateFile, $viewData, $basePath);
    }

    /**
     * Run shared content processors against renderable content fields.
     */
    protected function processRenderableContent(array $data): array
    {
        if (!isset($data['content']) || !is_string($data['content']) || $data['content'] === '') {
            return $data;
        }

        $context = [
            'content'       => $data['content'],
            'template_data' => $data,
        ];

        $processors = $this->app->adext('content', 'render', $context) ?: [];
        $content = $data['content'];

        foreach ($processors as $processor) {
            if (isset($processor['output']) && is_string($processor['output'])) {
                $content = $processor['output'];
                $context['content'] = $content;
            }
        }

        $data['content'] = $content;
        return $data;
    }

    /**
     * Assemble global data available to every public page.
     */
    protected function buildGlobalData(array $routeData = []): array
    {
        $siteName = $this->getSiteName();
        $themeRegions = $this->buildThemeRegions();

        return [
            'site' => [
                'name'        => $siteName,
                'url'         => $this->app->get('flight.base_url') ?? '/',
                'description' => $this->getSetting('CMS.siteByline') ?? '',
                'logo'        => $this->getSetting('CMS.logo') ?? '',
                'favicon'     => $this->getSetting('CMS.favicon') ?? '/favicon.ico',
                'copyright'   => $this->getSetting('CMS.copyright') ?? '&copy; ' . date('Y') . ' ' . $siteName,
            ],
            'header' => [
                'title' => '', // Set in render()
                'meta'  => [],
                'og'    => [],
                'css'   => $this->collectPackageAssets('head', 'css'),
            ],
            'footer' => [
                'js'    => $this->collectPackageAssets('footer', 'js'),
            ],
            'nav' => $this->getNavigation('primary'),
            'nav_footer' => $this->getNavigation('footer'),
            'before_content' => $themeRegions['before_content'] ?? '',
            'after_content' => $themeRegions['after_content'] ?? '',
            'theme_options' => $this->getThemeOptions(),
            'breadcrumbs' => $this->buildBreadcrumbs($routeData),
            'theme_regions' => $themeRegions,
        ];
    }

    /**
     * Collect registered package assets from adext and resolve theme override paths.
     *
     * For each registered file, checks for a theme override at:
     *   public/themes/{active_theme}/assets/{vendor}/{package}/{file}
     * Falls back to the package default at:
     *   public/assets/{vendor}/{package}/{file}
     *
     * @param string $type adext type ('head' or 'footer')
     * @param string $name adext name ('css' or 'js')
     * @return string[] Browser-relative URLs
     */
    protected function collectPackageAssets(string $type, string $name): array
    {
        $registered = $this->app->adext($type, $name) ?: [];
        $activeTheme = $this->getActiveThemeName();
        $publicPath = rtrim((string) (defined('PROJECT_ROOT') ? PROJECT_ROOT : dirname(__DIR__, 5)), '/') . '/public/';
        $urls = [];

        foreach ($registered as $entry) {
            $vendor = $entry['vendor'] ?? '';
            $package = $entry['package'] ?? '';
            $files = $entry['files'] ?? [];

            if ($vendor === '' || $package === '') {
                continue;
            }

            foreach ($files as $file) {
                $overridePath = $publicPath . 'themes/' . $activeTheme
                    . '/assets/' . $vendor . '/' . $package . '/' . $file;

                if (is_file($overridePath)) {
                    $urls[] = '/themes/' . $activeTheme
                        . '/assets/' . $vendor . '/' . $package . '/' . $file;
                } else {
                    $urls[] = '/assets/' . $vendor . '/' . $package . '/' . $file;
                }
            }
        }

        return $urls;
    }

    /**
     * Build a basic breadcrumb trail from the request path.
     */
    protected function buildBreadcrumbs(array $routeData = []): array
    {
        $uri = trim($this->app->request()->getVar('REQUEST_URI') ?? '/', '/');

        if ($uri === '' || $uri === '/') {
            return [];
        }

        $segments = explode('/', $uri);
        $crumbs = [['label' => 'Home', 'url' => '/']];
        $path = '';
        $pageTitle = $routeData['title'] ?? $routeData['archive_title'] ?? null;

        foreach ($segments as $i => $segment) {
            $path .= '/' . $segment;
            $isLast = ($i === count($segments) - 1);
            $label = ($isLast && $pageTitle)
                ? $pageTitle
                : ucwords(str_replace(['-', '_'], ' ', $segment));

            $crumbs[] = [
                'label' => $label,
                'url' => $isLast ? null : $path,
            ];
        }

        return $crumbs;
    }

    /**
     * Get a setting value from flight-settings (DB-backed).
     */
    protected function getSetting(string $key): ?string
    {
        return $this->app->settings()->get($key);
    }

    /**
     * Get the site name from settings with fallback.
     */
    protected function getSiteName(): string
    {
        return $this->getSetting('CMS.siteName')
            ?? $this->app->get('CMS.siteName')
            ?? 'Pubvana';
    }

    /**
     * Get all active theme options as a nested array.
     */
    protected function getThemeOptions(): array
    {
        $info = $this->getActiveThemeInfo();
        $defaults = $this->buildThemeOptionDefaults($info['provides']['options'] ?? []);

        try {
            $active = $this->app->themes()->getActive();
            if (!$active) {
                return $defaults;
            }

            $flat = $this->app->themes()->getThemeOptions((int) $active->id);
            $nested = $defaults;

            foreach ($flat as $key => $value) {
                $parts = explode('.', (string) $key);
                if (count($parts) === 2) {
                    $nested[$parts[0]][$parts[1]] = $value;
                } else {
                    $nested[$key] = $value;
                }
            }

            return $nested;
        } catch (\Throwable) {
            return $defaults;
        }
    }

    /**
     * Build all rendered theme regions.
     */
    protected function buildThemeRegions(): array
    {
        $regions = [
            'header' => '',
            'footer' => '',
            'navbar' => '',
            'before_content' => '',
            'after_content' => '',
        ];

        foreach ($this->getActiveThemeInfo()['provides']['regions'] ?? [] as $region) {
            $id = str_replace('-', '_', (string) ($region['id'] ?? ''));
            if ($id !== '' && !array_key_exists($id, $regions)) {
                $regions[$id] = '';
            }
        }

        try {
            return array_replace($regions, $this->app->regions()->buildAllRegions());
        } catch (\Throwable) {
            return $regions;
        }
    }

    /**
     * Get a navigation tree for a group.
     */
    protected function getNavigation(string $group = 'primary'): array
    {
        try {
            return $this->app->navigation()->getTree($group);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Normalize a stored public-relative asset path into a browser-safe URL.
     */
    protected function publicAssetUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }

        if (preg_match('#^(?:https?:)?//#i', $path)) {
            return $path;
        }

        $baseUrl = (string) ($this->app->get('flight.base_url') ?? '');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Get the active theme name from settings with fallback.
     */
    protected function getActiveThemeName(): string
    {
        try {
            $active = $this->app->themes()->getActive();
            if ($active && !empty($active->folder)) {
                return (string) $active->folder;
            }
        } catch (\Throwable) {
        }

        return $this->app->get('active_theme') ?? 'default';
    }

    /**
     * Read the active theme's pubvana.json metadata.
     */
    protected function getActiveThemeInfo(): array
    {
        $path = rtrim((string) PROJECT_ROOT, '/')
            . '/themes/'
            . $this->getActiveThemeName()
            . '/pubvana.json';

        if (!is_file($path)) {
            return [];
        }

        return json_decode((string) file_get_contents($path), true) ?? [];
    }

    /**
     * Build nested default theme options from the theme manifest.
     */
    protected function buildThemeOptionDefaults(array $optionDefs): array
    {
        $defaults = [];

        foreach ($optionDefs as $key => $def) {
            if (($def['type'] ?? '') === 'group') {
                $defaults[$key] = [];
                foreach (($def['fields'] ?? []) as $fieldKey => $fieldDef) {
                    $defaults[$key][$fieldKey] = $fieldDef['default'] ?? '';
                }
                continue;
            }

            $defaults[$key] = $def['default'] ?? '';
        }

        return $defaults;
    }
}
