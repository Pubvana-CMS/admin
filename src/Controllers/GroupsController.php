<?php

declare(strict_types=1);

namespace Pubvana\Admin\Controllers;

use Enlivenapp\FlightShield\Models\AuthGroup;
use Enlivenapp\FlightShield\Models\AuthGroupPermission;
use Enlivenapp\FlightShield\Models\AuthPermission;

class GroupsController extends AdminController
{
    protected function findGroup(int $id): ?AuthGroup
    {
        $group = new AuthGroup($this->app->get('db'));
        $group->eq('id', $id)->find();

        return $group->isHydrated() ? $group : null;
    }

    protected function allPermissions(): array
    {
        return (new AuthPermission($this->app->get('db')))->findAll();
    }

    protected function getGroupPermissions(string $alias): array
    {
        $records = (new AuthGroupPermission($this->app->get('db')))
            ->eq('group_alias', $alias)
            ->findAll();

        return array_map(fn($r) => $r->permission_alias, $records);
    }

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

    public function index(): void
    {
        $groups = (new AuthGroup($this->app->get('db')))->findAll();

        $this->render('groups/index', [
            'pageTitle' => 'Groups',
            'groups'    => $groups,
        ]);
    }

    public function create(): void
    {
        $this->render('groups/form', [
            'pageTitle'        => 'Create Group',
            'group'            => null,
            'permissions'      => $this->allPermissions(),
            'groupPermissions' => [],
        ]);
    }

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
