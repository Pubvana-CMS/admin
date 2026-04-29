<?php
/**
 * User list page.
 *
 * @var string $pageTitle
 * @var array  $users
 * @var int    $page
 * @var int    $pages
 * @var int    $total
 */
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Users (<?= $total ?>)</h3>
        <div class="card-actions">
            <a href="/admin/users/create" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>New User
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Groups</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-secondary text-center">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u->id ?></td>
                            <td><?= htmlspecialchars($u->username ?? '') ?></td>
                            <td><?= htmlspecialchars(implode(', ', $u->getGroups())) ?></td>
                            <td>
                                <?php if ($u->isBanned()): ?>
                                    <span class="badge bg-orange-lt" title="<?= htmlspecialchars($u->getBanMessage() ?? '') ?>">Banned</span>
                                <?php elseif ($u->isActivated()): ?>
                                    <span class="badge bg-green-lt">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-red-lt">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-secondary"><?= htmlspecialchars($u->last_active ?? '—') ?></td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="/admin/users/<?= $u->id ?>/edit" class="btn btn-sm">Edit</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#delete-modal-<?= $u->id ?>">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">Page <?= $page ?> of <?= $pages ?></p>
        <ul class="pagination m-0 ms-auto">
            <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
                <a class="page-link" href="/admin/users?page=<?= $page - 1 ?>">
                    <i class="ti ti-chevron-left"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <li class="page-item<?= $i === $page ? ' active' : '' ?>">
                    <a class="page-link" href="/admin/users?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item<?= $page >= $pages ? ' disabled' : '' ?>">
                <a class="page-link" href="/admin/users?page=<?= $page + 1 ?>">
                    <i class="ti ti-chevron-right"></i>
                </a>
            </li>
        </ul>
    </div>
    <?php endif; ?>
</div>

<!-- Delete confirmation modals -->
<?php if (!empty($users)): ?>
    <?php foreach ($users as $u): ?>
    <div class="modal modal-blur fade" id="delete-modal-<?= $u->id ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-status bg-danger"></div>
                <div class="modal-body text-center py-4">
                    <i class="ti ti-alert-triangle icon mb-2 text-danger icon-lg"></i>
                    <h3>Are you sure?</h3>
                    <div class="text-secondary">
                        This will delete <strong><?= htmlspecialchars($u->username ?? '') ?></strong>. This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn w-100" data-bs-dismiss="modal">Cancel</button>
                            </div>
                            <div class="col">
                                <form method="post" action="/admin/users/<?= $u->id ?>/delete">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger w-100">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
