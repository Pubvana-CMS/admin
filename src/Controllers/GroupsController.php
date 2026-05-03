<?php

declare(strict_types=1);

namespace Pubvana\Admin\Controllers;

use Enlivenapp\FlightShield\Models\AuthGroup;
use Enlivenapp\FlightShield\Models\AuthGroupPermission;
use Enlivenapp\FlightShield\Models\AuthPermission;

/**
 * Admin group management — CRUD and permission sync for auth groups.
 */
class GroupsController extends AdminController
{
    /**
     * Find a group by ID, or null if not found.
     *
     * @param int $id Group ID
     * @return AuthGroup|null
     */
    protected function findGroup(int $id): ?AuthGroup
    {
        $group = new AuthGroup($this->app->get('db'));
        $group->eq('id', $id)->find();

        return $group->isHydrated() ? $group : null;
    }

    /**
     * @return AuthPermission[]
     */
    protected function allPermissions(): array
    {
        return (new AuthPermission($this->app->get('db')))->findAll();
    }

    /**
     * Get all permission aliases assigned to a group.
     *
     * @param string $alias Group alias
     * @return string[]
     */
    protected function getGroupPermissions(string $alias): array
    {
        $records = (new AuthGroupPermission($this->app->get('db')))
            ->eq('group_alias', $alias)
            ->findAll();

        return array_map(fn($r) => $r->permission_alias, $records);
    }

    /**
     * Replace all permissions for a group with the given set.
     *
     * @param string   $alias       Group alias
     * @param string[] $permissions Permission aliases to assign
     * @return void
     */
    protected function syncGroupPermissions(string $alias, array $permissions): void
    {
        $db = $this->app->get('db');

        // Remove existing
        $existing = (new AuthGroupPermission($db))
            ->eq('group_alias', $alias)
            ->findAll();

        foreach ($existing as $record) {
            $record->delete();
        }

        // Insert new
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        foreach ($permissions as $perm) {
            $record = new AuthGroupPermission($db);
            $record->group_alias      = $alias;
            $record->permission_alias = $perm;
            $record->created_at       = $now;
            $record->insert();
        }
    }

    /**
     * List all groups.
     *
     * @return void
     */
    public function index(): void
    {
        $groups = (new AuthGroup($this->app->get('db')))->findAll();

        $this->render('groups/index', [
            'pageTitle' => 'Groups',
            'groups'    => $groups,
        ]);
    }

    /**
     * Show the create group form.
     *
     * @return void
     */
    public function create(): void
    {
        $this->render('groups/form', [
            'pageTitle'        => 'Create Group',
            'group'            => null,
            'permissions'      => $this->allPermissions(),
            'groupPermissions' => [],
        ]);
    }

    /**
     * Store a new group from POST data.
     *
     * @return void
     */
    public function store(): void
    {
        $post = $this->app->request()->data;
        $now  = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $group = new AuthGroup($this->app->get('db'));
        $alias = strtolower(trim($post->alias ?? ''));
        $alias = preg_replace('/[^a-z0-9_-]/', '_', $alias);
        $alias = preg_replace('/_+/', '_', $alias);
        $alias = trim($alias, '_');
        $group->alias       = $alias;
        $group->title       = trim($post->title ?? '');
        $group->description = trim($post->description ?? '') ?: null;
        $group->created_at  = $now;
        $group->updated_at  = $now;
        $group->insert();

        $selectedPerms = (array) ($post->permissions ?? []);
        if (!empty($selectedPerms)) {
            $this->syncGroupPermissions($group->alias, $selectedPerms);
        }

        $this->app->redirect('/admin/groups');
    }

    /**
     * Show the edit form for a group.
     *
     * @param string $id Group ID
     * @return void
     */
    public function edit(string $id): void
    {
        $group = $this->findGroup((int) $id);

        if ($group === null || $group->alias === 'superadmin') {
            $this->app->redirect('/admin/groups');
            return;
        }

        $this->render('groups/form', [
            'pageTitle'        => 'Edit Group',
            'group'            => $group,
            'permissions'      => $this->allPermissions(),
            'groupPermissions' => $this->getGroupPermissions($group->alias),
        ]);
    }

    /**
     * Update an existing group from POST data.
     *
     * @param string $id Group ID
     * @return void
     */
    public function update(string $id): void
    {
        $group = $this->findGroup((int) $id);

        if ($group === null || $group->alias === 'superadmin') {
            $this->app->redirect('/admin/groups');
            return;
        }

        $post = $this->app->request()->data;

        $group->title       = trim($post->title ?? $group->title);
        $group->description = trim($post->description ?? '') ?: null;
        $group->updated_at  = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $group->save();

        $selectedPerms = (array) ($post->permissions ?? []);
        $this->syncGroupPermissions($group->alias, $selectedPerms);

        $this->app->redirect('/admin/groups');
    }

    /**
     * Delete a group and its permission assignments.
     *
     * @param string $id Group ID
     * @return void
     */
    public function delete(string $id): void
    {
        $group = $this->findGroup((int) $id);

        if ($group !== null && $group->alias !== 'superadmin') {
            // Remove group permissions
            $existing = (new AuthGroupPermission($this->app->get('db')))
                ->eq('group_alias', $group->alias)
                ->findAll();

            foreach ($existing as $record) {
                $record->delete();
            }

            $group->delete();
        }

        $this->app->redirect('/admin/groups');
    }
}
