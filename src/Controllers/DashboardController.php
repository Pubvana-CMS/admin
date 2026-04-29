<?php

declare(strict_types=1);

namespace Pubvana\Admin\Controllers;

use Enlivenapp\FlightShield\Services\UserStats;

class DashboardController extends AdminController
{
    public function index(): void
    {
        $stats = new UserStats($this->app->get('db'));
        $user  = $this->app->auth()->user();

        $totalUsers    = $stats->totalUsers();
        $activeUsers   = $stats->activeUsers();
        $activePercent = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0;
        $percentChange = $stats->newUsersPercentChange();
        $newThisMonth  = $stats->newUsersThisMonth();
        $newLastMonth  = $stats->newUsersLastMonth();
        $logins        = $stats->loginAttempts(30);
        $usersByMonth  = $stats->newUsersByMonth(12);

        $this->render('dashboard/index', [
            'pageTitle'      => 'Dashboard',
            'username'       => $user->username ?? 'Admin',
            'totalUsers'     => $totalUsers,
            'activeUsers'    => $activeUsers,
            'activePercent'  => $activePercent,
            'percentChange'  => $percentChange,
            'newThisMonth'   => $newThisMonth,
            'newLastMonth'   => $newLastMonth,
            'logins'         => $logins,
            'usersByMonth'   => $usersByMonth,
        ]);

    }
}
