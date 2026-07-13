<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
use App\Services\BranchPermissionMatrixService;
use App\Services\PermissionMatrixService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => config('app.super_admin.email')],
            [
                'name' => config('app.super_admin.name'),
                'password' => Hash::make(config('app.super_admin.password')),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@globallife.in'],
            ['name' => 'Demo Admin', 'password' => Hash::make('Password!123'), 'status' => 'active', 'email_verified_at' => now()]
        );
        $admin->syncRoles(['admin']);

        $subAdmin = User::firstOrCreate(
            ['email' => 'blog-subadmin@globallife.in'],
            ['name' => 'Blog Sub Admin', 'password' => Hash::make('Password!123'), 'status' => 'active', 'email_verified_at' => now()]
        );
        $subAdmin->syncRoles(['sub_admin']);
        $subAdmin->syncPermissions(PermissionMatrixService::permissionsForModule('blog'));

        $jhansi = City::where('slug', 'jhansi')->first();
        $delhi = City::where('slug', 'delhi')->first();

        $branchManager = User::firstOrCreate(
            ['email' => 'branch.manager@globallife.in'],
            [
                'name' => 'Demo Branch Manager',
                'mobile' => '+91 90000 11111',
                'password' => Hash::make('Password!123'),
                'status' => 'active',
                'commission_percentage' => 30,
                'email_verified_at' => now(),
            ]
        );
        $branchManager->syncRoles(['branch_manager']);
        $branchManager->syncPermissions(BranchPermissionMatrixService::permissionsForModule('commission-partners'));

        foreach ([$jhansi, $delhi] as $city) {
            if ($city && ! $branchManager->branchCities()->where('city_id', $city->id)->exists()) {
                $branchManager->branchCities()->attach($city->id);
            }
        }

        $partners = [
            ['email' => 'jhansi.managerA@globallife.in', 'name' => 'Jhansi Manager A', 'city' => $jhansi],
            ['email' => 'jhansi.managerB@globallife.in', 'name' => 'Jhansi Manager B', 'city' => $jhansi],
            ['email' => 'delhi.managerA@globallife.in', 'name' => 'Delhi Manager A', 'city' => $delhi],
        ];

        foreach ($partners as $partnerData) {
            $partner = User::firstOrCreate(
                ['email' => $partnerData['email']],
                [
                    'name' => $partnerData['name'],
                    'password' => Hash::make('Password!123'),
                    'status' => 'active',
                    'commission_percentage' => 20,
                    'created_by' => $branchManager->id,
                    'email_verified_at' => now(),
                ]
            );
            $partner->syncRoles(['commission_partner']);

            if ($partnerData['city'] && ! $partner->cities()->where('city_id', $partnerData['city']->id)->exists()) {
                $partner->cities()->attach($partnerData['city']->id);
            }
        }

        $vipMember = User::firstOrCreate(
            ['email' => 'vip-member@globallife.in'],
            ['name' => 'Demo VIP Member', 'password' => Hash::make('Password!123'), 'status' => 'active', 'email_verified_at' => now()]
        );
        $vipMember->syncRoles(['vip_member']);
    }
}
