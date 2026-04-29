<?php
/**
 * Groups list page.
 *
 * @var string $pageTitle
 * @var array  $groups
 */
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Groups</h3>
        <div class="card-actions">
            <a href="/admin/groups/create" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>New Group
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($groups)): ?>
                    <tr>
                        <td colspan="4" class="text-secondary text-center">No groups found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($groups as $g): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($g->alias) ?></code></td>
                            <td><?= htmlspecialchars($g->title) ?></td>
                            <td class="text-secondary"><?= htmlspecialchars($g->description ?? '—') ?></td>
                            <td>
                                <?php if ($g->alias !== 'superadmin'): ?>
                                <div class="btn-list flex-nowrap">
                                    <a href="/admin/groups/<?= $g->id ?>/edit" class="btn btn-sm">Edit</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#delete-modal-<?= $g->id ?>">
                                        Delete
                                    </button>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete confirmation modals -->
<?php if (!empty($groups)): ?>
    <?php foreach ($groups as $g): ?>
    <div class="modal modal-blur fade" id="delete-modal-<?= $g->id ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-status bg-danger"></div>
                <div class="modal-body text-center py-4">
                    <i class="ti ti-alert-triangle icon mb-2 text-danger icon-lg"></i>
                    <h3>Are you sure?</h3>
                    <div class="text-secondary">
                        This will delete the <strong><?= htmlspecialchars($g->title) ?></strong> group
                        and remove all its permission assignments.
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn w-100" data-bs-dismiss="modal">Cancel</button>
                            </div>
                            <div class="col">
                                <form method="post" action="/admin/groups/<?= $g->id ?>/delete">
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
