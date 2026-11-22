<?php

use App\Services\PermissionMatrixService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * The `employee` role and the HR modules of the Sub Admin permission matrix.
 *
 * Production runs migrations, not seeders, so the rows the seeder would create
 * are created here too; also seeds sensible HR defaults (a General shift and
 * the usual Indian leave types) so the module works on day one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        $permissions = [];
        foreach (PermissionMatrixService::HR_MODULES as $module) {
            foreach (PermissionMatrixService::permissionsForModule($module) as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
                $permissions[] = $permission;
            }
        }

        Role::where('name', 'admin')->where('guard_name', 'web')->first()?->givePermissionTo($permissions);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if (! DB::table('shifts')->exists()) {
            DB::table('shifts')->insert([
                'name' => 'General (10 AM - 7 PM)',
                'start_time' => '10:00:00',
                'end_time' => '19:00:00',
                'grace_minutes' => 15,
                'half_day_minutes' => 240,
                'full_day_minutes' => 480,
                'weekly_offs' => json_encode([0]),
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! DB::table('leave_types')->exists()) {
            $types = [
                ['Casual Leave', 'CL', 12, true, false, 0],
                ['Sick Leave', 'SL', 6, true, false, 0],
                ['Earned Leave', 'EL', 15, true, true, 30],
                ['Unpaid Leave', 'LWP', 0, false, false, 0],
            ];
            foreach ($types as [$name, $code, $days, $paid, $carry, $max]) {
                DB::table('leave_types')->insert([
                    'name' => $name, 'code' => $code, 'days_per_year' => $days, 'is_paid' => $paid,
                    'carry_forward' => $carry, 'max_carry_forward' => $max, 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissions = [];
        foreach (PermissionMatrixService::HR_MODULES as $module) {
            $permissions = [...$permissions, ...PermissionMatrixService::permissionsForModule($module)];
        }

        Permission::whereIn('name', $permissions)->where('guard_name', 'web')->delete();
        Role::where('name', 'employee')->where('guard_name', 'web')->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
