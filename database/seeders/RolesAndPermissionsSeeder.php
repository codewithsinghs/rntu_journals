<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'view menus', 'create menus', 'edit menus', 'delete menus',
            'view journals', 'create journals', 'edit journals', 'delete journals',
            'view announcements', 'create announcements', 'edit announcements', 'delete announcements',
            'view home-content', 'create home-content', 'edit home-content', 'delete home-content',
            'view settings', 'edit settings',
            'view medias', 'create medias', 'edit medias', 'delete medias',
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'view dashboard',
            'view menus', 'create menus', 'edit menus', 'delete menus',
            'view journals', 'create journals', 'edit journals', 'delete journals',
            'view announcements', 'create announcements', 'edit announcements', 'delete announcements',
            'view home-content', 'create home-content', 'edit home-content', 'delete home-content',
            'view settings', 'edit settings',
            'view medias', 'create medias', 'edit medias', 'delete medias',
        ]);

        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            'view dashboard',
            'view menus', 'create menus', 'edit menus',
            'view journals', 'create journals', 'edit journals',
            'view announcements', 'create announcements', 'edit announcements',
            'view home-content', 'edit home-content',
            'view medias', 'create medias',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'view dashboard',
            'view menus',
            'view journals',
            'view announcements',
            'view home-content',
            'view medias',
        ]);

        $this->command->info('Roles and permissions seeded successfully.');
    }
}