<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public const string SuperAdmin = 'Super Admin';

    public const string SchoolAdmin = 'School Admin';

    public const string Teacher = 'Teacher';

    public const string Student = 'Student';

    public const string ParentGuardian = 'Parent/Guardian';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            self::SuperAdmin,
            self::SchoolAdmin,
            self::Teacher,
            self::Student,
            self::ParentGuardian,
        ] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }
    }
}
