<?php
/**
 * Dashboard — main admin landing page.
 *
 * @var string $pageTitle
 * @var string $username
 * @var int    $totalUsers
 * @var int    $activeUsers
 * @var int    $activePercent
 * @var float  $percentChange
 * @var int    $newThisMonth
 * @var int    $newLastMonth
 * @var array  $logins
 * @var array  $usersByMonth
 */
?>

<!-- Top row: Welcome | Total Users | Active Users -->
<div class="row row-deck row-cards">

    <!-- Welcome card -->
    <div class="col-sm-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="row gy-3">
                    <div class="col-12 col-sm-6 d-flex flex-column">
                        <h3 class="h2">Welcome back, <?= htmlspecialchars($username) ?></h3>
                        <p class="text-secondary">You have 0 new messages and 0 new notifications.</p>

                        <div class="row g-5 mt-auto">
                            <div class="col-auto">
                                <div class="subheader">Today's Sales</div>
                                <div class="d-flex align-items-baseline">
                                    <div class="h3 me-2">&mdash;</div>
                                </div>
                                <div class="progress progress-sm" style="width: 80px;">
                                    <div class="progress-bar bg-success" style="width: 0%" role="progressbar"></div>
                                </div>
                            </div>

                            <div class="col-auto">
                                <div class="subheader">Growth Rate</div>
                                <div class="d-flex align-items-baseline">
                                    <div class="h3 me-2">&mdash;</div>
                                </div>
                                <div class="progress progress-sm" style="width: 80px;">
                                    <div class="progress-bar bg-danger" style="width: 0%" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 d-flex justify-content-center align-items-center">
                        <!-- reserved -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Total Users</div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2"><?= number_format($totalUsers) ?></div>
                    <div class="me-auto">
                        <?php if ($percentChange != 0): ?>
                            <span class="text-<?= $percentChange > 0 ? 'green' : 'red' ?> d-inline-flex align-items-center lh-1">
                                <?= $percentChange > 0 ? '+' : '' ?><?= $percentChange ?>%
                                <i class="ti ti-arrow-<?= $percentChange > 0 ? 'up' : 'down' ?> ms-1" style="font-size:.75rem"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-secondary mt-2">
                    <?php if ($newThisMonth > 0): ?>
                        <?= number_format($newThisMonth) ?> users increased from last month
                    <?php else: ?>
                        No new users this month
                    <?php endif; ?>
                </div>
            </div>
            <div id="chart-total-users" class="position-relative"></div>
        </div>
    </div>

    <!-- Active Users -->
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Active Users</div>
                <div class="d-flex align-items-baseline mb-2">
                    <div class="h1 mb-0 me-2"><?= number_format($activeUsers) ?></div>
                </div>
                <div id="chart-active-users" class="position-relative"></div>
            </div>
        </div>
    </div>

</div>

<!-- Login activity row -->
<div class="row row-deck row-cards mt-3">
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="subheader">Login Attempts (30 days)</div>
                <div class="h1 mb-3"><?= number_format($logins['total']) ?></div>
                <div class="d-flex">
                    <span class="text-green me-3">
                        <i class="ti ti-check me-1"></i><?= number_format($logins['success']) ?> successful
                    </span>
                    <span class="text-red">
                        <i class="ti ti-x me-1"></i><?= number_format($logins['failed']) ?> failed
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<?php
    $monthLabels = array_keys($usersByMonth);
    $monthData   = array_values($usersByMonth);
?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    if (!window.ApexCharts) return;

    // Total Users — sparkline from real monthly registration data
    new ApexCharts(document.getElementById('chart-total-users'), {
        chart: {
            type: 'line',
            fontFamily: 'inherit',
            height: 96,
            sparkline: { enabled: true },
            animations: { enabled: false },
        },
        stroke: {
            width: 2,
            lineCap: 'round',
            curve: 'smooth',
        },
        series: [{
            name: 'New users',
            data: <?= json_encode($monthData) ?>
        }],
        xaxis: {
            categories: <?= json_encode($monthLabels) ?>
        },
        tooltip: { theme: 'dark' },
        grid: { strokeDashArray: 4 },
        colors: ['var(--tblr-primary)'],
        legend: { show: false },
    }).render();

    // Active Users — radialBar
    new ApexCharts(document.getElementById('chart-active-users'), {
        chart: {
            type: 'radialBar',
            fontFamily: 'inherit',
            height: 192,
            sparkline: { enabled: true },
            animations: { enabled: false },
        },
        plotOptions: {
            radialBar: {
                startAngle: -120,
                endAngle: 120,
                hollow: {
                    margin: 16,
                    size: '50%',
                },
                dataLabels: {
                    show: true,
                    name: { show: false },
                    value: {
                        offsetY: 0,
                        fontSize: '24px',
                    },
                },
            },
        },
        series: [<?= (int) $activePercent ?>],
        labels: ['Active'],
        tooltip: { enabled: false },
        colors: ['var(--tblr-primary)'],
        legend: { show: false },
    }).render();
});
</script>
