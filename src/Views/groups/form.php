<?php
/**
 * Group create/edit form.
 *
 * @var string      $pageTitle
 * @var object|null $group            Null for create, AuthGroup entity for edit
 * @var array       $permissions      All available AuthPermission entities
 * @var array       $groupPermissions Current group's permission aliases
 */

$isEdit = $group !== null;
$action = $isEdit ? "/admin/groups/{$group->id}/update" : '/admin/groups/store';

// Group permissions by prefix
$permGroups = [];
foreach ($permissions as $p) {
    $parts = explode('.', $p->alias, 2);
    $prefix = ucfirst($parts[0]);
    $permGroups[$prefix][] = $p;
}
ksort($permGroups);
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $isEdit ? 'Edit' : 'Create' ?> Group</h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="alias">Name</label>
                <input type="text" class="form-control" id="alias" name="alias"
                       value="<?= htmlspecialchars($group->alias ?? '') ?>"
                       <?= $isEdit ? 'readonly' : 'required' ?>>
                <span class="form-hint">Unique identifier, lowercase (e.g. editor, moderator)</span>
            </div>

            <div class="mb-3">
                <label class="form-label" for="title">Title</label>
                <input type="text" class="form-control" id="title" name="title"
                       value="<?= htmlspecialchars($group->title ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <input type="text" class="form-control" id="description" name="description"
                       value="<?= htmlspecialchars($group->description ?? '') ?>">
            </div>

            <?php if (!empty($permGroups)): ?>
            <hr class="my-4">

            <h3 class="mb-3">Permissions</h3>
            <?php foreach ($permGroups as $groupName => $perms):
                $pgSlug = strtolower($groupName);
                $wildcardAlias = $pgSlug . '.*';
                $hasWildcard = in_array($wildcardAlias, $groupPermissions, true);
            ?>
                <div class="mb-3" x-data="{ allChecked: <?= $hasWildcard ? 'true' : 'false' ?> }">
                    <h4 class="mb-2 mt-3">
                        <label class="form-check d-inline-flex align-items-center">
                            <input class="form-check-input me-2" type="checkbox"
                                   name="permissions[]" value="<?= htmlspecialchars($wildcardAlias) ?>"
                                   x-model="allChecked"
                                   @change="document.querySelectorAll('.gperm-<?= $pgSlug ?>').forEach(el => el.checked = allChecked)">
                            <span><?= htmlspecialchars($groupName) ?></span>
                            <span class="text-secondary fw-normal ms-2" style="font-size:.85em">All (<?= htmlspecialchars($wildcardAlias) ?>)</span>
                        </label>
                    </h4>
                    <div class="row">
                        <?php foreach ($perms as $p): ?>
                            <div class="col-md-4 col-lg-3">
                                <label class="form-check mb-2">
                                    <input class="form-check-input gperm-<?= $pgSlug ?>" type="checkbox"
                                           name="permissions[]" value="<?= htmlspecialchars($p->alias) ?>"
                                           <?= in_array($p->alias, $groupPermissions, true) || $hasWildcard ? 'checked' : '' ?>
                                           @change="allChecked = [...document.querySelectorAll('.gperm-<?= $pgSlug ?>')].every(el => el.checked)">
                                    <span class="form-check-label"><?= htmlspecialchars($p->alias) ?></span>
                                    <?php if ($p->description): ?>
                                        <span class="form-check-description"><?= htmlspecialchars($p->description) ?></span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($groupName !== array_key_last($permGroups)): ?>
                    <hr class="my-3">
                <?php endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Update' : 'Create' ?> Group
                </button>
                <a href="/admin/groups" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
