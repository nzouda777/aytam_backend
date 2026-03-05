<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ShieldSeeder extends Seeder
{
    /**
     * Seed roles, permissions, and create default admin users.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ----------------------------------------------------------------
        // 1. Create the super_admin role (full access via Gate::before)
        // ----------------------------------------------------------------
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web']
        );

        // ----------------------------------------------------------------
        // 2. Create the admin role with specific permissions
        // ----------------------------------------------------------------
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web']
        );

        // Define admin permissions per resource
        $adminPermissions = [
            // Orphans: full CRUD
            'view_orphan', 'view_any_orphan', 'create_orphan', 'update_orphan', 'delete_orphan',
            // Families: full CRUD
            'view_family', 'view_any_family', 'create_family', 'update_family', 'delete_family',
            // Donations: view & create only (cannot delete)
            'view_donation', 'view_any_donation', 'create_donation', 'update_donation',
            // Sponsorships: view & create only
            'view_sponsorship', 'view_any_sponsorship', 'create_sponsorship', 'update_sponsorship',
            // Users: view only (cannot create, edit, or delete)
            'view_user', 'view_any_user',
            // Pages & Widgets
            'page_Dashboard',
            'widget_StatsOverview', 'widget_DonationsChart', 'widget_SponsorshipsChart', 'widget_LatestDonations',
        ];

        foreach ($adminPermissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }

        $adminRole->syncPermissions($adminPermissions);

        // ----------------------------------------------------------------
        // 3. Create default Super Admin user
        // ----------------------------------------------------------------
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@yateem.org'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // ----------------------------------------------------------------
        // 4. Create default Admin user
        // ----------------------------------------------------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@yateem.org'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
        $admin->assignRole($adminRole);

        $this->command->info('✅ Shield roles and admin users created successfully!');
        $this->command->info('   Super Admin: superadmin@yateem.org / password');
        $this->command->info('   Admin:       admin@yateem.org / password');
    }
}
