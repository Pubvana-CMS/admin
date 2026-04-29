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
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <p class="text-secondary">No settings found.</p>
    </div>
</div>
<?php endif; ?>
