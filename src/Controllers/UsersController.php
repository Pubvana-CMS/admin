<?php

declare(strict_types=1);

namespace Pubvana\Admin\Controllers;

use Enlivenapp\FlightShield\Models\AuthGroup;
use Enlivenapp\FlightShield\Models\AuthGroupPermission;
use Enlivenapp\FlightShield\Models\AuthPermission;
use Enlivenapp\FlightShield\Models\User;
use Enlivenapp\FlightShield\Models\UserIdentity;

/**
 * Admin user management — CRUD, group/permission sync, ban/unban.
 */
class UsersController extends AdminController
{
    protected function userModel(): User
    {
        return new User($this->app->get('db'));
    }

    protected function identityModel(): UserIdentity
    {
        return new UserIdentity($this->app->get('db'));
    }

    /**
     * @return AuthGroup[]
     */
    protected function allGroups(bool $includeSuperadmin = false): array
    {
        $groups = (new AuthGroup($this->app->get('db')))->findAll();

        if (!$includeSuperadmin) {
            $groups = array_filter($groups, fn($g) => $g->alias !== 'superadmin');
        }

        return array_values($groups);
    }

    /**
     * @return AuthPermission[]
     */
    protected function allPermissions(): array
    {
        return (new AuthPermission($this->app->get('db')))->findAll();
    }

    /**
     * Get all permissions granted to a user via their groups.
     * Returns a flat array of permission aliases, with wildcards expanded.
     */
    protected function getGroupPermissionsForUser(User $user): array
    {
        $db = $this->app->get('db');
        $userGroups = $user->getGroups();
        $groupPerms = [];

        foreach ($userGroups as $groupAlias) {
            $records = (new AuthGroupPermission($db))
                ->eq('group_alias', $groupAlias)
                ->findAll();

            foreach ($records as $r) {
                $groupPerms[] = $r->permission_alias;
            }
        }

        return array_unique($groupPerms);
    }

    /**
     * Resolve which permission aliases are effectively granted by group permissions.
     * Expands wildcards (e.g. admin.*) against the full permission list.
     *
     * @param array $permissions All AuthPermission entities
     * @param array $groupPerms  Flat array of group permission aliases (may contain wildcards)
     * @return array Flat array of granted permission aliases
     */
    protected function resolveGroupGrantedPermissions(array $permissions, array $groupPerms): array
    {
        $granted = [];

        foreach ($permissions as $p) {
            if (in_array($p->alias, $groupPerms, true) || in_array('*', $groupPerms, true)) {
                $granted[] = $p->alias;
                continue;
            }

            foreach ($groupPerms as $gp) {
                if (str_contains($gp, '*')) {
                    $pattern = str_replace('*', '.*', preg_quote($gp, '/'));
                    if (preg_match('/^' . $pattern . '$/', $p->alias)) {
                        $granted[] = $p->alias;
                        break;
                    }
                }
            }
        }

        return $granted;
    }

    /**
     * Whether the current user is a superadmin.
     */
    protected function isSuperadmin(): bool
    {
        return $this->app->auth()->user()->inGroup('superadmin');
    }

    /**
     * List all users with pagination.
     */
    public function index(): void
    {
        $page    = (int) ($this->app->request()->query->page ?? 1);
        $perPage = 20;
        $isSuperadmin = $this->isSuperadmin();
        $userModel = $this->userModel();
        $users   = $userModel->findAllPaginated($page, $perPage, $isSuperadmin);
        $total   = $userModel->countAll($isSuperadmin);
        $pages   = (int) ceil($total / $perPage);

        $this->render('users/index', [
            'pageTitle' => 'Users',
            'users'     => $users,
            'page'      => $page,
            'pages'     => $pages,
            'total'     => $total,
        ]);
    }

    /**
     * Show the create user form.
     */
    public function create(): void
    {
        $this->render('users/form', [
            'pageTitle' => 'Create User',
            'user'      => null,
            'email'     => '',
            'groups'    => $this->allGroups($this->isSuperadmin()),
            'userGroups' => [],
        ]);
    }

    /**
     * Store a new user.
     */
    public function store(): void
    {
        $post = $this->app->request()->data;
        $username = trim($post->username ?? '');
        $email    = trim($post->email ?? '');
        $password = $post->password ?? '';
        $group    = $post->group ?? 'user';

        $userModel = $this->userModel();
        $user = $userModel->createUser($username, $email, $password, $group);

        $this->app->redirect('/admin/users');
    }

    /**
     * Edit a user.
     *
     * Adext context for `users.edit.tabs`:
     *   - user_id    (int)    — ID of the user being edited
     *   - return_url (string) — URL to redirect back to after save
     *
     * @param string $id User ID
     */
    public function edit(string $id): void
    {
        $isSuperadmin = $this->isSuperadmin();
        $user = $this->userModel()->findById((int) $id, $isSuperadmin);

        if ($user === null) {
            $this->app->redirect('/admin/users');
            return;
        }

        $identity = $this->identityModel()->getEmailIdentity($user);

        $tabContext = [
            'user_id'    => (int) $id,
            'return_url' => '/users/' . $id . '/edit',
        ];

        $this->render('users/form', [
            'pageTitle'       => 'Edit User',
            'user'            => $user,
            'email'           => $identity->secret ?? '',
            'groups'          => $this->allGroups($isSuperadmin),
            'userGroups'      => $user->getGroups(),
            'permissions'       => $permissions = $this->allPermissions(),
            'userPermissions'   => $user->getPermissions(),
            'userDenied'        => $user->getDeniedPermissions(),
            'groupPermissions'  => $groupPerms = $this->getGroupPermissionsForUser($user),
            'groupGranted'      => $this->resolveGroupGrantedPermissions($permissions, $groupPerms),
            'pluginTabs'        => $this->app->adext('page', 'users.edit.tabs', $tabContext),
        ]);
    }

    /**
     * Update an existing user.
     *
     * Handles username, email, password, active status,
     * ban state, group sync, and permission sync.
     *
     * @param string $id User ID
     */
    public function update(string $id): void
    {
        $isSuperadmin = $this->isSuperadmin();
        $userModel = $this->userModel();
        $user = $userModel->findById((int) $id, $isSuperadmin);

        if ($user === null) {
            $this->app->redirect('/admin/users');
            return;
        }

        $post = $this->app->request()->data;

        $user->username = trim($post->username ?? $user->username);
        $user->active   = (bool) ($post->active ?? false);
        $user->updateUser();

        // Update email if changed
        $identityModel = $this->identityModel();
        $identity = $identityModel->getEmailIdentity($user);
        $newEmail = trim($post->email ?? '');
        if ($identity !== null && $newEmail !== '' && $newEmail !== $identity->secret) {
            $identity->secret     = $newEmail;
            $identity->updated_at = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            $identity->save();
        }

        // Update password if provided
        $newPassword = $post->password ?? '';
        if ($newPassword !== '') {
            $passwords = new \Enlivenapp\FlightShield\Passwords\Passwords();
            if ($identity !== null) {
                $identity->secret2    = $passwords->hash($newPassword);
                $identity->updated_at = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
                $identity->save();
            }
        }

        // Ban / unban
        $banned = (bool) ($post->banned ?? false);
        if ($banned && !$user->isBanned()) {
            $user->ban($post->ban_message ?? null);
            $user->updateUser();
        } elseif (!$banned && $user->isBanned()) {
            $user->unBan();
            $user->updateUser();
        } elseif ($banned && $user->isBanned()) {
            // Update ban message if changed
            $newMessage = $post->ban_message ?? null;
            if ($newMessage !== $user->getBanMessage()) {
                $user->ban($newMessage);
                $user->updateUser();
            }
        }

        // Sync groups
        $selectedGroups = (array) ($post->groups ?? []);
        if (!empty($selectedGroups)) {
            $user->syncGroups($selectedGroups);
        }

        // Sync permissions (grants and denies from radio states)
        $permStates = (array) ($post->perm_state ?? []);
        $grants = [];
        $denies = [];
        foreach ($permStates as $alias => $state) {
            if ($state === 'grant') {
                $grants[] = $alias;
            } elseif ($state === 'deny') {
                $denies[] = $alias;
            }
            // 'inherit' = no entry
        }
        $user->syncPermissions($grants, $denies);

        $this->app->redirect('/admin/users');
    }

    /**
     * Delete a user.
     *
     * @param string $id User ID
     */
    public function delete(string $id): void
    {
        $isSuperadmin = $this->isSuperadmin();
        $userModel = $this->userModel();
        $user = $userModel->findById((int) $id, $isSuperadmin);

        if ($user !== null) {
            $user->softDelete();
        }

        $this->app->redirect('/admin/users');
    }
}
