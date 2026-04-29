<?php
/**
 * Pubvana Admin Layout
 *
 * Two-tier topbar: brand bar + nav bar, then content area.
 * Tabler (Bootstrap 5) + Alpine.js for UI state, HTMX ready.
 *
 * @var string $content    Page content (rendered by AdminController::render)
 * @var string $pageTitle  Page title
 * @var string $siteName   Site name from config
 * @var object|null $user  Authenticated user entity
 * @var string $userGroups Comma-separated group names
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> - <?= htmlspecialchars($siteName ?? 'Pubvana') ?></title>
    <link rel="stylesheet" href="/assets/admin/dist/css/tabler.min.css"/>
    <link rel="stylesheet" href="/assets/admin/dist/css/tabler-icons.min.css"/>
    <link rel="stylesheet" href="/assets/admin/css/admin.css"/>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <script src="/assets/admin/dist/js/alpine.min.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@4/es2021/jodit.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jodit@4/es2021/jodit.min.js"></script>
</head>
<body
    class="layout-fluid"
    x-data="{ darkMode: localStorage.getItem('pv-dark-mode') === '1' }"
    x-init="$watch('darkMode', val => { localStorage.setItem('pv-dark-mode', val ? '1' : '0'); val ? document.body.setAttribute('data-bs-theme','dark') : document.body.removeAttribute('data-bs-theme') }); if(darkMode) document.body.setAttribute('data-bs-theme','dark')"
>
<div class="page">

    <!-- ===== Top bar: branding + user ===== -->
    <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">

            <!-- Brand -->
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="https://pubvanacms.com" target="_blank" rel="noopener" class="me-2">
                    <img src="/assets/admin/img/pubvana-logo.png" alt="Pubvana" width="28" height="28">
                </a>
                <a href="/admin">
                    <span class="nav-link-title"><?= htmlspecialchars($siteName ?? 'Pubvana') ?></span>
                </a>
            </h1>

            <!-- Mobile toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
                    aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Right side: dark mode + user -->
            <div class="navbar-nav flex-row order-md-last">

                <!-- Dark / light toggle -->
                <div class="nav-item d-flex align-items-center">
                    <a class="nav-link px-2" href="#" @click.prevent="darkMode = !darkMode"
                       title="Toggle dark mode">
                        <i class="ti" :class="darkMode ? 'ti-sun' : 'ti-moon'" style="font-size:1.25rem"></i>
                    </a>
                </div>

                <!-- User menu -->
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0 ps-2" data-bs-toggle="dropdown"
                       aria-label="User menu">
                        <span class="avatar avatar-sm rounded-circle bg-blue-lt">
                            <?= strtoupper(substr($user->username ?? 'A', 0, 1)) ?>
                        </span>
                        <div class="d-none d-xl-block ps-2">
                            <div><?= htmlspecialchars($user->username ?? 'Admin') ?></div>
                            <div class="mt-1 small text-secondary"><?= htmlspecialchars($userGroups) ?></div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <a href="/admin/profile" class="dropdown-item">
                            <i class="ti ti-user me-2"></i>Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="post" action="/auth/logout">
                            <?= csrf_field() ?>
                            <button type="submit" class="dropdown-item">
                                <i class="ti ti-logout me-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ===== Nav bar: categories ===== -->
    <header class="navbar-expand-md">
        <div class="collapse navbar-collapse" id="navbar-menu">
            <div class="navbar">
                <div class="container-xl">
                    <ul class="navbar-nav">

                        <!-- Dashboard (no dropdown) -->
                        <li class="nav-item">
                            <a class="nav-link" href="/admin">
                                <span class="nav-link-icon"><i class="ti ti-dashboard"></i></span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>

                        <!-- Content -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"
                               data-bs-auto-close="outside" role="button" aria-expanded="false">
                                <span class="nav-link-icon"><i class="ti ti-file-text"></i></span>
                                <span class="nav-link-title">Content</span>
                            </a>
                            <div class="dropdown-menu">
                                <?php if (!empty($menuSlots['content'])): ?>
                                    <?php foreach ($menuSlots['content'] as $item): ?>
                                        <?php if (!empty($item['submenu'])): ?>
                                            <div class="dropend">
                                                <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                                    <i class="ti <?= htmlspecialchars($item['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($item['label']) ?>
                                                </a>
                                                <div class="dropdown-menu">
                                                    <?php foreach ($item['submenu'] as $sub): ?>
                                                        <a class="dropdown-item" href="<?= htmlspecialchars($sub['url']) ?>">
                                                            <i class="ti <?= htmlspecialchars($sub['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($sub['label']) ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <a class="dropdown-item" href="<?= htmlspecialchars($item['url']) ?>">
                                                <i class="ti <?= htmlspecialchars($item['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($item['label']) ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="dropdown-header">No content modules installed</span>
                                <?php endif; ?>
                            </div>
                        </li>

                        <!-- Appearance -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"
                               data-bs-auto-close="outside" role="button" aria-expanded="false">
                                <span class="nav-link-icon"><i class="ti ti-palette"></i></span>
                                <span class="nav-link-title">Appearance</span>
                            </a>
                            <div class="dropdown-menu">
                                <?php if (!empty($menuSlots['appearance'])): ?>
                                    <?php foreach ($menuSlots['appearance'] as $item): ?>
                                        <?php if (!empty($item['submenu'])): ?>
                                            <div class="dropend">
                                                <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                                    <i class="ti <?= htmlspecialchars($item['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($item['label']) ?>
                                                </a>
                                                <div class="dropdown-menu">
                                                    <?php foreach ($item['submenu'] as $sub): ?>
                                                        <a class="dropdown-item" href="<?= htmlspecialchars($sub['url']) ?>">
                                                            <i class="ti <?= htmlspecialchars($sub['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($sub['label']) ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <a class="dropdown-item" href="<?= htmlspecialchars($item['url']) ?>">
                                                <i class="ti <?= htmlspecialchars($item['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($item['label']) ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="dropdown-header">No appearance modules installed</span>
                                <?php endif; ?>
                            </div>
                        </li>

                        <!-- Tools -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"
                               data-bs-auto-close="outside" role="button" aria-expanded="false">
                                <span class="nav-link-icon"><i class="ti ti-tool"></i></span>
                                <span class="nav-link-title">Tools</span>
                            </a>
                            <div class="dropdown-menu">
                                <?php if (!empty($menuSlots['tools'])): ?>
                                    <?php foreach ($menuSlots['tools'] as $item): ?>
                                        <?php if (!empty($item['submenu'])): ?>
                                            <div class="dropend">
                                                <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                                    <i class="ti <?= htmlspecialchars($item['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($item['label']) ?>
                                                </a>
                                                <div class="dropdown-menu">
                                                    <?php foreach ($item['submenu'] as $sub): ?>
                                                        <a class="dropdown-item" href="<?= htmlspecialchars($sub['url']) ?>">
                                                            <i class="ti <?= htmlspecialchars($sub['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($sub['label']) ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <a class="dropdown-item" href="<?= htmlspecialchars($item['url']) ?>">
                                                <i class="ti <?= htmlspecialchars($item['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($item['label']) ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="dropdown-header">No tool modules installed</span>
                                <?php endif; ?>
                            </div>
                        </li>

                        <!-- Settings -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"
                               data-bs-auto-close="outside" role="button" aria-expanded="false">
                                <span class="nav-link-icon"><i class="ti ti-settings"></i></span>
                                <span class="nav-link-title">Settings</span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="/admin/settings">
                                    <i class="ti ti-adjustments me-2"></i>General
                                </a>
                                <a class="dropdown-item" href="/admin/users">
                                    <i class="ti ti-users me-2"></i>Users
                                </a>
                                <a class="dropdown-item" href="/admin/groups">
                                    <i class="ti ti-shield me-2"></i>Groups
                                </a>
                                <?php foreach ($menuSlots['settings'] ?? [] as $item): ?>
                                    <?php if (!empty($item['submenu'])): ?>
                                        <div class="dropend">
                                            <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                                <i class="ti <?= htmlspecialchars($item['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($item['label']) ?>
                                            </a>
                                            <div class="dropdown-menu">
                                                <?php foreach ($item['submenu'] as $sub): ?>
                                                    <a class="dropdown-item" href="<?= htmlspecialchars($sub['url']) ?>">
                                                        <i class="ti <?= htmlspecialchars($sub['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($sub['label']) ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <a class="dropdown-item" href="<?= htmlspecialchars($item['url']) ?>">
                                            <i class="ti <?= htmlspecialchars($item['icon'] ?? 'ti-point') ?> me-2"></i><?= htmlspecialchars($item['label']) ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- ===== Page content ===== -->
    <div class="page-wrapper">

        <!-- Page header -->
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="page-pretitle">Pubvana CMS</div>
                <h2 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h2>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-xl">
                <?= $content ?>
            </div>
        </div>

    </div>

</div>

<script src="/assets/admin/dist/js/apexcharts.min.js"></script>
<script src="/assets/admin/dist/js/tabler.min.js"></script>
<script src="/assets/admin/dist/js/htmx.min.js"></script>
</body>
</html>
