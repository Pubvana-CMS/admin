<?php
/**
 * User create/edit form.
 *
 * @var string      $pageTitle
 * @var object|null $user       Null for create, User entity for edit
 * @var string      $email      Current email address
 * @var array       $groups     All available AuthGroup entities
 * @var array       $userGroups      Current user's group aliases
 * @var array       $permissions      All available AuthPermission entities (edit only)
 * @var array       $userPermissions  Current user's granted permission aliases (edit only)
 * @var array       $userDenied       Current user's denied permission aliases (edit only)
 * @var array       $pluginTabs       Slot contributions for users.edit.tabs (edit only)
 */

$permissions      = $permissions ?? [];
$userDenied       = $userDenied ?? [];
$groupPermissions = $groupPermissions ?? [];
$pluginTabs       = $pluginTabs ?? [];

/**
 * Check if a permission is granted by group permissions (supports wildcards).
 */
function permissionGrantedByGroup(string $permission, array $groupPerms): bool {
    if (in_array($permission, $groupPerms, true) || in_array('*', $groupPerms, true)) {
        return true;
    }
    foreach ($groupPerms as $gp) {
        if (str_contains($gp, '*')) {
            $pattern = str_replace('*', '.*', preg_quote($gp, '/'));
            if (preg_match('/^' . $pattern . '$/', $permission)) {
                return true;
            }
        }
    }
    return false;
}
$userPermissions = $userPermissions ?? [];

$isEdit = $user !== null;
$action = $isEdit ? "/admin/users/{$user->id}/update" : '/admin/users/store';
$hasTabs = $isEdit && !empty($pluginTabs);

$allowedFieldTypes = ['string', 'text', 'integer', 'double', 'boolean', 'media_image'];
?>
<div class="card">
    <?php if ($hasTabs): ?>
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-user">User</a>
            </li>
            <?php foreach ($pluginTabs as $tabKey => $tab): ?>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-<?= htmlspecialchars($tabKey) ?>">
                        <?= htmlspecialchars($tab['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php else: ?>
    <div class="card-header">
        <h3 class="card-title"><?= $isEdit ? 'Edit' : 'Create' ?> User</h3>
    </div>
    <?php endif; ?>

    <div class="card-body">
        <?php if ($hasTabs): ?>
        <div class="tab-content">
            <div class="tab-pane active show" id="tab-user">
        <?php endif; ?>

        <!-- Shield user form -->
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= htmlspecialchars($user->username ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">
                    Password<?= $isEdit ? ' <span class="text-secondary fw-normal">(leave blank to keep current)</span>' : '' ?>
                </label>
                <input type="password" class="form-control" id="password" name="password"
                       <?= $isEdit ? '' : 'required' ?>>
            </div>

            <?php if ($isEdit): ?>
                <div class="mb-3">
                    <label class="form-label">Groups</label>
                    <?php foreach ($groups as $g): ?>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="groups[]" value="<?= htmlspecialchars($g->alias) ?>"
                                   <?= in_array($g->alias, $userGroups, true) ? 'checked' : '' ?>>
                            <span class="form-check-label"><?= htmlspecialchars($g->title) ?></span>
                            <?php if ($g->description): ?>
                                <span class="form-check-description"><?= htmlspecialchars($g->description) ?></span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <input type="hidden" name="active" value="0">
                    <label class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" value="1"
                               <?= $user->isActivated() ? 'checked' : '' ?>>
                        <span class="form-check-label">Active</span>
                        <span class="form-check-description">Deactivated users cannot log in</span>
                    </label>
                </div>

                <div class="mb-3" x-data="{ banned: <?= $user->isBanned() ? 'true' : 'false' ?> }">
                    <input type="hidden" name="banned" value="0">
                    <label class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="banned" value="1"
                               <?= $user->isBanned() ? 'checked' : '' ?>
                               @change="banned = $el.checked">
                        <span class="form-check-label">Banned</span>
                        <span class="form-check-description">Banned users cannot log in and see a ban message</span>
                    </label>
                    <div class="mt-2" x-show="banned" x-transition>
                        <label class="form-label" for="ban_message">Ban Reason</label>
                        <textarea class="form-control" id="ban_message" name="ban_message" rows="2"
                                  placeholder="Optional reason shown to the user"><?= htmlspecialchars($user->getBanMessage() ?? '') ?></textarea>
                    </div>
                </div>
                <?php if (!empty($permissions)):
                    // Group permissions by prefix (e.g. admin.access → "Admin", users.list → "Users")
                    $permGroups = [];
                    foreach ($permissions as $p) {
                        $parts = explode('.', $p->alias, 2);
                        $group = ucfirst($parts[0]);
                        $permGroups[$group][] = $p;
                    }
                    ksort($permGroups);
                ?>
                <hr class="my-4">

                <div class="mb-3">
                    <div class="accordion" id="permissions-accordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#permissions-body"
                                        aria-expanded="false" aria-controls="permissions-body">
                                    Direct Permissions
                                    <span class="ms-2 text-secondary fw-normal" style="font-size:.85em">
                                        Override group-level permissions for this user
                                    </span>
                                </button>
                            </h2>
                            <div id="permissions-body" class="accordion-collapse collapse"
                                 data-bs-parent="#permissions-accordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>Permission</th>
                                                    <th class="w-1 text-center">Group</th>
                                                    <th class="w-1 text-center">Inherit</th>
                                                    <th class="w-1 text-center">Grant</th>
                                                    <th class="w-1 text-center">Deny</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($permGroups as $groupName => $perms): ?>
                                                    <tr class="bg-light">
                                                        <td colspan="5"><strong><?= htmlspecialchars($groupName) ?></strong></td>
                                                    </tr>
                                                    <?php foreach ($perms as $p):
                                                        $isGranted = in_array($p->alias, $userPermissions, true);
                                                        $isDenied  = in_array($p->alias, $userDenied, true);
                                                        $state     = $isDenied ? 'deny' : ($isGranted ? 'grant' : 'inherit');
                                                    ?>
                                                    <?php $hasGroup = permissionGrantedByGroup($p->alias, $groupPermissions); ?>
                                                    <tr>
                                                        <td>
                                                            <code><?= htmlspecialchars($p->alias) ?></code>
                                                            <?php if ($p->description): ?>
                                                                <div class="text-secondary small"><?= htmlspecialchars($p->description) ?></div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if ($hasGroup): ?>
                                                                <i class="ti ti-check text-success" title="Granted by group"></i>
                                                            <?php else: ?>
                                                                <i class="ti ti-x text-danger" title="Not granted by group"></i>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <input class="form-check-input" type="radio"
                                                                   name="perm_state[<?= htmlspecialchars($p->alias) ?>]"
                                                                   value="inherit" <?= $state === 'inherit' ? 'checked' : '' ?>>
                                                        </td>
                                                        <td class="text-center">
                                                            <input class="form-check-input" type="radio"
                                                                   name="perm_state[<?= htmlspecialchars($p->alias) ?>]"
                                                                   value="grant" <?= $state === 'grant' ? 'checked' : '' ?>>
                                                        </td>
                                                        <td class="text-center">
                                                            <input class="form-check-input" type="radio"
                                                                   name="perm_state[<?= htmlspecialchars($p->alias) ?>]"
                                                                   value="deny" <?= $state === 'deny' ? 'checked' : '' ?>>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="mb-3">
                    <label class="form-label" for="group">Group</label>
                    <select class="form-select" id="group" name="group">
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= htmlspecialchars($g->alias) ?>"
                                    <?= $g->alias === 'user' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g->title) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Update' : 'Create' ?> User
                </button>
                <a href="/admin/users" class="btn btn-link">Cancel</a>
            </div>
        </form>

        <?php if ($hasTabs): ?>
            </div><!-- /tab-pane user -->

            <?php foreach ($pluginTabs as $tabKey => $tab): ?>
                <div class="tab-pane" id="tab-<?= htmlspecialchars($tabKey) ?>">
                    <?php
                    $tabFields = $tab['fields'] ?? [];
                    $postUrl   = $tab['post_url'] ?? '';
                    $returnUrl = $tab['return_url'] ?? '';
                    $renderableFields = array_filter($tabFields, fn($f) => in_array($f['type'] ?? 'string', $allowedFieldTypes, true));
                    ?>
                    <?php if (!empty($renderableFields) && $postUrl): ?>
                    <form method="post" action="<?= htmlspecialchars($postUrl) ?>">
                        <?= csrf_field() ?>
                        <?php if ($returnUrl): ?>
                            <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
                        <?php endif; ?>

                        <?php foreach ($renderableFields as $key => $field):
                            $label = $field['title'] ?? $key;
                            $desc  = $field['description'] ?? null;
                            $value = $field['value'] ?? '';
                            $type  = $field['type'] ?? 'string';
                        ?>
                            <div class="mb-3">
                                <label class="form-label" for="tab-field-<?= htmlspecialchars($key) ?>">
                                    <?= htmlspecialchars($label) ?>
                                </label>
                                <?php if ($type === 'boolean'): ?>
                                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="0">
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                               id="tab-field-<?= htmlspecialchars($key) ?>"
                                               name="<?= htmlspecialchars($key) ?>"
                                               value="1"
                                               <?= $value ? 'checked' : '' ?>>
                                    </label>
                                <?php elseif ($type === 'text'): ?>
                                    <textarea class="form-control" rows="4"
                                              id="tab-field-<?= htmlspecialchars($key) ?>"
                                              name="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars((string) $value) ?></textarea>
                                <?php elseif ($type === 'integer'): ?>
                                    <input type="number" class="form-control" step="1"
                                           id="tab-field-<?= htmlspecialchars($key) ?>"
                                           name="<?= htmlspecialchars($key) ?>"
                                           value="<?= htmlspecialchars((string) $value) ?>">
                                <?php elseif ($type === 'double'): ?>
                                    <input type="number" class="form-control" step="any"
                                           id="tab-field-<?= htmlspecialchars($key) ?>"
                                           name="<?= htmlspecialchars($key) ?>"
                                           value="<?= htmlspecialchars((string) $value) ?>">
                                <?php elseif ($type === 'media_image'): ?>
                                    <?= \Flight::media()->picker($key, (string) $value) ?>
                                <?php else: ?>
                                    <input type="text" class="form-control"
                                           id="tab-field-<?= htmlspecialchars($key) ?>"
                                           name="<?= htmlspecialchars($key) ?>"
                                           value="<?= htmlspecialchars((string) $value) ?>">
                                <?php endif; ?>
                                <?php if ($desc !== null): ?>
                                    <span class="form-hint"><?= htmlspecialchars($desc) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save <?= htmlspecialchars($tab['label']) ?></button>
                        </div>
                    </form>
                    <?php else: ?>
                        <p class="text-secondary">No fields available.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </div><!-- /tab-content -->
        <?php endif; ?>
    </div>
</div>
