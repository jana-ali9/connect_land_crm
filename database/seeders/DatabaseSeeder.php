<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Roles;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tables = ['roles', 'users','buildings','lands','clients','units','services','contracts','invoices','expenseOffers'];
        $permissions[]=["name" => 'read dashboard', "description" => "show dashboard", "groub_by" => 0];
        foreach ($tables as $index => $table) {
            $actions = ['read', 'create', 'update', 'delete'];
            foreach ($actions as $action) {
                $permissions[] = [
                    "name" => "{$action} {$table}",
                    "description" => ucfirst($action) . " permission for {$table}",
                    "groub_by" => $index + 1
                ];
            }
        }
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
        $superAdminRole = Roles::firstOrCreate([
            'name' => 'Super Admin',
            'description' => 'Has all permissions',
        ]);
        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@demo.com',
        ], [
            'name' => 'Super Admin',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => $superAdminRole->id,
        ]);
        $superAdminRole->permissions()->sync(Permission::pluck('id')->toArray());
        $superAdmin->refresh();
    }
}
