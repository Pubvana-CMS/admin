<?php
/**
 * Settings page — tabs by class, form fields per class.
 *
 * @var string[] $classes     All setting class names
 * @var string   $activeClass Currently selected class
 * @var array    $fields      key => [value, type, title, description] for the active class
 */

$allowedTypes = ['string', 'integer', 'double', 'boolean'];
?>

<!-- Class tabs -->
<?php if (!empty($classes)): ?>
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <?php foreach ($classes as $class): ?>
                <li class="nav-item">
                    <a class="nav-link<?= $class === $activeClass ? ' active' : '' ?>"
                       href="/admin/settings/<?= htmlspecialchars($class) ?>">
                        <?= htmlspecialchars($class) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card-body">
        <?php if ($activeClass === 'FrontPage'): ?>
            <?php
            $blogPrefix = Flight::pluginLoader()->routePrefix('pubvana/blog');
            $pagesPrefix = Flight::pluginLoader()->routePrefix('pubvana/pages');
            $currentRoute = $fields['route']['value'] ?? $blogPrefix;
            $fpType = 'blog';
            $fpSlug = '';
            $fpCustom = '';
            if ($currentRoute === $blogPrefix) {
                $fpType = 'blog';
            } elseif (str_starts_with($currentRoute, $pagesPrefix . '/')) {
                $fpType = 'page';
                $fpSlug = substr($currentRoute, strlen($pagesPrefix) + 1);
            } else {
                $fpType = 'custom';
                $fpCustom = $currentRoute;
            }
            ?>
            <form method="post" action="/admin/settings/<?= htmlspecialchars($activeClass) ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="front_page_type" value="blog" <?= $fpType === 'blog' ? 'checked' : '' ?>>
                        <span class="form-check-label">Blog index</span>
                    </label>
                </div>

                <div class="mb-3">
                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="front_page_type" value="page" <?= $fpType === 'page' ? 'checked' : '' ?>>
                        <span class="form-check-label">A static page</span>
                    </label>
                    <?php if (!empty($pages)): ?>
                    <div class="ms-4 mt-2">
                        <select name="front_page_slug" class="form-select" style="max-width:400px">
                            <?php foreach ($pages as $p): ?>
                                <option value="<?= htmlspecialchars($p->slug) ?>" <?= $fpSlug === $p->slug ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p->title) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="front_page_type" value="custom" <?= $fpType === 'custom' ? 'checked' : '' ?>>
                        <span class="form-check-label">Custom route</span>
                    </label>
                    <div class="ms-4 mt-2">
                        <input type="text" name="front_page_custom" class="form-control" style="max-width:400px"
                               placeholder="/some/route"
                               value="<?= htmlspecialchars($fpCustom) ?>">
                        <span class="form-hint">Enter the route path (e.g. /store, /docs).</span>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>

        <?php else: ?>
            <?php
            $renderableFields = array_filter($fields, fn($f) => in_array($f['type'], $allowedTypes, true));
            ?>
            <?php if (!empty($renderableFields)): ?>
            <form method="post" action="/admin/settings/<?= htmlspecialchars($activeClass) ?>">
                <?= csrf_field() ?>

                <?php foreach ($renderableFields as $key => $field):
                    $label = $field['title'] ?? $key;
                    $desc  = $field['description'] ?? null;
                ?>
                    <div class="mb-3">
                        <label class="form-label" for="field-<?= htmlspecialchars($key) ?>">
                            <?= htmlspecialchars($label) ?>
                        </label>
                        <?php if ($field['type'] === 'boolean'): ?>
                            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="0">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       id="field-<?= htmlspecialchars($key) ?>"
                                       name="<?= htmlspecialchars($key) ?>"
                                       value="1"
                                       <?= $field['value'] ? 'checked' : '' ?>>
                            </label>
                        <?php elseif ($field['type'] === 'integer'): ?>
                            <input type="number" class="form-control" step="1"
                                   id="field-<?= htmlspecialchars($key) ?>"
                                   name="<?= htmlspecialchars($key) ?>"
                                   value="<?= htmlspecialchars((string)($field['value'] ?? '')) ?>">
                        <?php elseif ($field['type'] === 'double'): ?>
                            <input type="number" class="form-control" step="any"
                                   id="field-<?= htmlspecialchars($key) ?>"
                                   name="<?= htmlspecialchars($key) ?>"
                                   value="<?= htmlspecialchars((string)($field['value'] ?? '')) ?>">
                        <?php else: ?>
                            <input type="text" class="form-control"
                                   id="field-<?= htmlspecialchars($key) ?>"
                                   name="<?= htmlspecialchars($key) ?>"
                                   value="<?= htmlspecialchars((string)($field['value'] ?? '')) ?>">
                        <?php endif; ?>
                        <?php if ($desc !== null): ?>
                            <span class="form-hint"><?= htmlspecialchars($desc) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
            <?php else: ?>
                <p class="text-secondary">No settings found for this group.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <p class="text-secondary">No settings found.</p>
    </div>
</div>
<?php endif; ?>
