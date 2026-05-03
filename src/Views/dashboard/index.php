<?php
/**
 * Dashboard — main admin landing page.
 *
 * @var string $pageTitle
 * @var string $username
 * @var array  $cards
 * @var array  $sections
 */
?>

<div class="row row-deck row-cards mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div>
                        <div class="subheader">Admin Overview</div>
                        <h2 class="mb-1">Welcome back, <?= htmlspecialchars($username) ?></h2>
                        <div class="text-secondary">A quick look at what is live, what is waiting, and what needs attention.</div>
                    </div>
                    <div class="text-secondary small">
                        Dashboard contributions come from installed Pubvana packages.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($cards)): ?>
<div class="row row-deck row-cards mb-3">
    <?php foreach ($cards as $card): ?>
        <?php $tone = htmlspecialchars($card['tone'] ?? 'primary'); ?>
        <div class="col-sm-6 col-xl-4 col-xxl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="subheader"><?= htmlspecialchars((string) $card['label']) ?></div>
                            <div class="h1 mb-1"><?= htmlspecialchars((string) $card['value']) ?></div>
                            <?php if (!empty($card['description'])): ?>
                                <div class="text-secondary"><?= htmlspecialchars((string) $card['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($card['icon'])): ?>
                            <span class="avatar avatar-md bg-<?= $tone ?>-lt text-<?= $tone ?>">
                                <i class="ti <?= htmlspecialchars((string) $card['icon']) ?>"></i>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($card['trend']) && is_array($card['trend'])): ?>
                        <div class="mt-3 text-secondary small">
                            <span class="text-<?= htmlspecialchars((string) ($card['trend']['direction'] === 'down' ? 'danger' : ($card['trend']['direction'] === 'up' ? 'success' : 'secondary'))) ?>">
                                <?= htmlspecialchars((string) ($card['trend']['value'] ?? '')) ?>
                            </span>
                            <?php if (!empty($card['trend']['label'])): ?>
                                <span><?= htmlspecialchars((string) $card['trend']['label']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($card['href'])): ?>
                    <div class="card-footer bg-transparent">
                        <a href="<?= htmlspecialchars((string) $card['href']) ?>" class="btn btn-sm btn-outline-secondary">Open</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($sections)): ?>
<div class="row row-deck row-cards">
    <?php foreach ($sections as $section): ?>
        <?php $tone = htmlspecialchars($section['tone'] ?? 'primary'); ?>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">
                            <?php if (!empty($section['icon'])): ?>
                                <i class="ti <?= htmlspecialchars((string) $section['icon']) ?> me-2 text-<?= $tone ?>"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars((string) $section['title']) ?>
                        </h3>
                        <?php if (!empty($section['description'])): ?>
                            <div class="text-secondary small"><?= htmlspecialchars((string) $section['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($section['href'])): ?>
                        <div class="card-actions">
                            <a href="<?= htmlspecialchars((string) $section['href']) ?>" class="btn btn-sm btn-outline-secondary">View all</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="list-group list-group-flush">
                    <?php if (($section['type'] ?? 'list') === 'actions'): ?>
                        <?php if (empty($section['items'])): ?>
                            <div class="list-group-item text-secondary"><?= htmlspecialchars((string) ($section['empty_state'] ?? 'Nothing to show yet.')) ?></div>
                        <?php else: ?>
                            <div class="card-body">
                                <div class="btn-list">
                                    <?php foreach ($section['items'] as $item): ?>
                                        <?php $emphasis = htmlspecialchars((string) ($item['emphasis'] ?? 'secondary')); ?>
                                        <a href="<?= htmlspecialchars((string) $item['href']) ?>" class="btn btn-outline-<?= $emphasis ?>">
                                            <?php if (!empty($item['icon'])): ?>
                                                <i class="ti <?= htmlspecialchars((string) $item['icon']) ?> me-1"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars((string) $item['label']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (empty($section['items'])): ?>
                            <div class="list-group-item text-secondary"><?= htmlspecialchars((string) ($section['empty_state'] ?? 'Nothing to show yet.')) ?></div>
                        <?php else: ?>
                            <?php foreach ($section['items'] as $item): ?>
                                <?php $emphasis = htmlspecialchars((string) ($item['emphasis'] ?? 'secondary')); ?>
                                <a href="<?= htmlspecialchars((string) ($item['href'] ?? '#')) ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div>
                                            <div class="fw-medium"><?= htmlspecialchars((string) $item['label']) ?></div>
                                            <?php if (!empty($item['meta'])): ?>
                                                <div class="text-secondary small"><?= htmlspecialchars((string) $item['meta']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-<?= $emphasis ?>-lt text-<?= $emphasis ?>">
                                            <?= htmlspecialchars(ucfirst((string) $emphasis)) ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
