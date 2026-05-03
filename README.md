[![Stable? Not Quite Yet](https://img.shields.io/badge/stable%3F-not%20quite%20yet-blue?style=for-the-badge)](https://packagist.org/packages/pubvana/admin)
[![License](https://img.shields.io/packagist/l/pubvana/admin?style=for-the-badge)](https://packagist.org/packages/pubvana/admin)
[![PHP Version](https://img.shields.io/packagist/php-v/pubvana/admin?style=for-the-badge)](https://packagist.org/packages/pubvana/admin)
[![Monthly Downloads](https://img.shields.io/packagist/dm/pubvana/admin?style=for-the-badge)](https://packagist.org/packages/pubvana/admin)
[![Total Downloads](https://img.shields.io/packagist/dt/pubvana/admin?style=for-the-badge)](https://packagist.org/packages/pubvana/admin)
[![GitHub Issues](https://img.shields.io/github/issues/Pubvana-CMS/admin?style=for-the-badge)](https://github.com/Pubvana-CMS/admin/issues)
[![Contributors](https://img.shields.io/github/contributors/Pubvana-CMS/admin?style=for-the-badge)](https://github.com/Pubvana-CMS/admin/graphs/contributors)
[![Latest Release](https://img.shields.io/github/v/release/Pubvana-CMS/admin?style=for-the-badge)](https://github.com/Pubvana-CMS/admin/releases)
[![Contributions Welcome](https://img.shields.io/badge/contributions-welcome-blue?style=for-the-badge)](https://github.com/Pubvana-CMS/admin/pulls)

# Pubvana Admin

**I noticed folks downloading some of these packages. I'm super grateful, Thank You!  I would like to let folks know until this notice disappears I'm doing a lot of breaking changes without worrying about them.  Once versions are up around 0.5.x things should settle down.**

Admin panel module for Pubvana CMS. Provides the admin shell, public theme renderer, dashboard, user/group management, settings UI, and slot-based content injection for plugin extensions.

## Requirements

- PHP 8.1+
- [Flight School](https://github.com/enlivenapp/flight-school) ^0.2
- [Flight Shield](https://github.com/enlivenapp/flight-shield) ^0.1

## Installation

```bash
composer require pubvana/admin
```

Enable in `app/config/config.php`:

```php
'plugins' => [
    'pubvana/admin' => [
        'enabled'  => true,
        'priority' => 40,
    ],
],
```

## Flight School config

This package uses Flight School's return-array config format. `src/Config/Config.php` returns the package defaults as an array, and Flight School stores that array under `pubvana.admin` on `$app`.

The current config includes `'routePrepend' => ''`, so routes from `src/Config/Routes.php` register at the site root. `AdminRoutes.php` is still grouped separately under `/admin`.

## Responsibilities

- Admin routes and layout
- Public base controllers used by blog/pages/theme rendering
- Dashboard cards/sections and shared public blocks
- User, group, and settings management

## License

MIT
