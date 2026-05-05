<?php

declare(strict_types=1);

namespace Pubvana\Admin;

use Enlivenapp\FlightSchool\PluginInterface;
use flight\Engine;
use flight\net\Router;

class Plugin implements PluginInterface
{
    public function register(Engine $app, Router $router, array $config = []): void
    {
        $app->adext('head', 'css', 'pubvana.admin.blocks', [
            'priority' => 5,
            'files'    => ['css/blocks.css'],
            'vendor'   => 'pubvana',
            'package'  => 'admin',
        ]);

        $app->adext('page', 'dashboard.sections', 'pubvana.admin', [
            'label'    => 'Admin',
            'priority' => 90,
            'callable' => static function (array $context): array {
                return [[
                    'id'          => 'quick-actions',
                    'title'       => 'Quick Actions',
                    'type'        => 'actions',
                    'icon'        => 'ti-bolt',
                    'description' => 'Common admin tasks you are likely to need next.',
                    'items'       => [
                        [
                            'label'    => 'New Page',
                            'href'     => '/pages/create',
                            'icon'     => 'ti-file-plus',
                            'emphasis' => 'primary',
                        ],
                        [
                            'label'    => 'New Post',
                            'href'     => '/blog/create',
                            'icon'     => 'ti-article',
                            'emphasis' => 'primary',
                        ],
                        [
                            'label'    => 'Review Comments',
                            'href'     => '/comments?status=pending',
                            'icon'     => 'ti-message-circle',
                            'emphasis' => 'warning',
                        ],
                        [
                            'label'    => 'Open 404 Manager',
                            'href'     => '/404-manager',
                            'icon'     => 'ti-link-off',
                            'emphasis' => 'danger',
                        ],
                        [
                            'label'    => 'Upload Media',
                            'href'     => '/media',
                            'icon'     => 'ti-photo-up',
                            'emphasis' => 'secondary',
                        ],
                    ],
                ]];
            },
        ]);

        $app->adext('block', 'available', 'pubvana.admin.toc', [
            'label'       => 'Table of Contents',
            'description' => 'Auto-generated from page headings',
            'provider'    => function (array $options) use ($app) {
                $uri = trim($app->request()->getVar('REQUEST_URI') ?? '', '/');
                $parts = explode('/', $uri);
                $content = '';

                $blogPrefix = ltrim($app->pluginLoader()->routePrefix('pubvana/blog'), '/');
                if (count($parts) === 2 && $parts[0] === $blogPrefix) {
                    $post = $app->blog()->findPostBySlug($parts[1]);
                    if ($post) {
                        $content = $post->content ?? '';
                    }
                }

                $headings = [];
                if ($content !== '') {
                    preg_match_all('/<h([2-4])[^>]*(?:id=["\']([^"\']*)["\'])?[^>]*>(.*?)<\\/h\\1>/is', $content, $matches, PREG_SET_ORDER);
                    foreach ($matches as $i => $match) {
                        $level = (int) $match[1];
                        $id = $match[2] !== '' ? $match[2] : 'heading-' . $i;
                        $text = strip_tags($match[3]);
                        $headings[] = [
                            'level' => $level,
                            'id'    => $id,
                            'text'  => $text,
                        ];
                    }
                }

                return [
                    'title'    => $options['title'] ?? 'Table of Contents',
                    'headings' => $headings,
                ];
            },
            'template'    => 'pubvana/admin/public/blocks/toc',
            'priority'    => 20,
            'options'     => [
                'title' => ['type' => 'input', 'label' => 'Title', 'default' => 'Table of Contents'],
            ],
        ]);

        $app->adext('block', 'available', 'pubvana.admin.search', [
            'label'       => 'Search Form',
            'description' => 'Site search form',
            'provider'    => fn(array $options) => [
                'action'      => $options['action'] ?? '/search',
                'label'       => $options['label'] ?? 'Search',
                'placeholder' => $options['placeholder'] ?? 'Search...',
                'button_text' => $options['button_text'] ?? 'Go',
            ],
            'template'    => 'pubvana/admin/public/blocks/search',
            'priority'    => 10,
            'options'     => [
                'action'      => ['type' => 'input', 'label' => 'Form Action URL', 'default' => '/search'],
                'label'       => ['type' => 'input', 'label' => 'Label', 'default' => 'Search'],
                'placeholder' => ['type' => 'input', 'label' => 'Placeholder', 'default' => 'Search...'],
                'button_text' => ['type' => 'input', 'label' => 'Button Text', 'default' => 'Go'],
            ],
        ]);

        $app->adext('block', 'available', 'pubvana.admin.text', [
            'label'       => 'Text Block',
            'description' => 'Free-form text or HTML content',
            'provider'    => fn(array $options) => [
                'title'   => $options['title'] ?? '',
                'content' => $options['content'] ?? '',
            ],
            'template'    => 'pubvana/admin/public/blocks/text',
            'priority'    => 20,
            'options'     => [
                'title'   => ['type' => 'input', 'label' => 'Title', 'default' => ''],
                'content' => ['type' => 'textarea', 'label' => 'Content', 'default' => ''],
            ],
        ]);
    }
}
