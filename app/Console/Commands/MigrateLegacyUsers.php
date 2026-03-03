<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyUsers extends Command
{
    // Artisan command signature & description
    protected $signature   = 'dts:migrate-legacy-users';
    protected $description = 'ETL command to migrate legacy dtsapp_office, auth_user, and dtsapp_profile records into the new user schema.';

    public function handle(): void
    {
        $this->info('Starting Legacy User Migration...');
        $this->newLine();

        DB::transaction(function () {
            $this->migrateOffices();
            $this->seedUserRolesAndGroups();
            $this->migrateUsers();
        });

        $this->newLine();
        $this->info('Legacy User Migration Complete!');
    }

    // --- OFFICES ---

    /** Migrate dtsapp_office → offices, preserving original IDs. */
    private function migrateOffices(): void
    {
        $this->info('Step 1: Migrating Offices...');

        $offices = DB::connection('mysql_legacy')
            ->table('dtsapp_office')
            ->get();

        $bar = $this->output->createProgressBar($offices->count());
        $bar->start();

        foreach ($offices as $old) {
            DB::table('offices')->updateOrInsert(
                ['id' => $old->id],
                [
                    'name'       => $old->name,
                    'code'       => $old->office_code,
                    'is_active'  => (bool) $old->status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  ✔ {$offices->count()} offices migrated.");
    }

    // --- SEED DEFAULTS ---

    /** Insert the base user_roles and user_groups needed before users are inserted. */
    private function seedUserRolesAndGroups(): void
    {
        $this->info('Step 2: Seeding Roles & Groups...');

        // Base roles — mirrors hostel format
        $roles = [
            ['id' => 1, 'name' => 'Administrator'],
            ['id' => 2, 'name' => 'Staff'],
        ];

        foreach ($roles as $role) {
            DB::table('user_roles')->updateOrInsert(['id' => $role['id']], array_merge($role, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Translate the old boolean columns into named groups
        $groups = [
            ['id' => 1, 'name' => 'Office Admins'],    // was office_admin = 1
            ['id' => 2, 'name' => 'Accounting Staff'],  // was accounting_access = 1
            ['id' => 3, 'name' => 'Records Staff'],     // was record_access = 1
        ];

        foreach ($groups as $group) {
            DB::table('user_groups')->updateOrInsert(['id' => $group['id']], array_merge($group, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->line('  ✔ Roles & Groups seeded.');
    }

    // --- USERS ---

    /** Merge auth_user + dtsapp_profile → users, then map boolean flags → user_group_members. */
    private function migrateUsers(): void
    {
        $this->info('Step 3: Migrating Users & Permissions...');

        // Pull both old tables in a single joined query for efficiency
        $users = DB::connection('mysql_legacy')
            ->table('auth_user as u')
            ->leftJoin('dtsapp_profile as p', 'u.id', '=', 'p.user_id')
            ->select(
                'u.id',
                'u.first_name',
                'u.last_name',
                'u.email',
                'u.username',
                'u.password',
                'u.is_active',
                'u.is_superuser',
                'u.date_joined',
                'p.middle_name',
                'p.contact as contact_number',
                'p.office_id',
                'p.office_admin',
                'p.accounting_access',
                'p.record_access'
            )
            ->get();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $old) {
            // Superusers get role 1 (Administrator), everyone else gets role 2 (Staff)
            $roleId = $old->is_superuser ? 1 : 2;

            DB::table('users')->updateOrInsert(
                ['id' => $old->id],
                [
                    'user_role_id'     => $roleId,
                    'office_id'        => $old->office_id,
                    'first_name'       => $old->first_name,
                    'last_name'        => $old->last_name,
                    'middle_name'      => $old->middle_name,
                    'display_name'     => trim("{$old->first_name} {$old->last_name}"),
                    'email'            => $old->email,
                    'username'         => $old->username,
                    // Intentionally carried over as-is; verified by a compatibility layer on login
                    'password'         => $old->password,
                    'contact_number'   => $old->contact_number,
                    'is_active'        => (bool) $old->is_active,
                    'created_at'       => $old->date_joined ?? now(),
                    'updated_at'       => $old->date_joined ?? now(),
                ]
            );

            // --- TRANSLATE BOOLEAN FLAGS → PIVOT ROWS ---
            $this->assignGroup($old->id, 1, $old->office_admin);       // Office Admins
            $this->assignGroup($old->id, 2, $old->accounting_access);  // Accounting Staff
            $this->assignGroup($old->id, 3, $old->record_access);      // Records Staff

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->line("  ✔ {$users->count()} users migrated with group permissions.");
    }

    // --- HELPERS ---

    /** Insert a user_group_members pivot row if the legacy flag was truthy. */
    private function assignGroup(int $userId, int $groupId, mixed $flag): void
    {
        if (!$flag) {
            return;
        }

        DB::table('user_group_members')->updateOrInsert(
            ['user_id' => $userId, 'user_group_id' => $groupId],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }
}
