<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            'Jhansi' => 'Uttar Pradesh',
            'Delhi' => 'Delhi',
            'Mumbai' => 'Maharashtra',
            'Bengaluru' => 'Karnataka',
            'Hyderabad' => 'Telangana',
            'Ahmedabad' => 'Gujarat',
            'Pune' => 'Maharashtra',
            'Gurgaon' => 'Haryana',
            'Indore' => 'Madhya Pradesh',
            'Lucknow' => 'Uttar Pradesh',
            'Jaipur' => 'Rajasthan',
            'Patna' => 'Bihar',
            'Surat' => 'Gujarat',
            'Coimbatore' => 'Tamil Nadu',
            'Guwahati' => 'Assam',
            'Bhopal' => 'Madhya Pradesh',
            'Nagpur' => 'Maharashtra',
            'Chandigarh' => 'Chandigarh',
            'Kochi' => 'Kerala',
        ];

        foreach ($cities as $name => $state) {
            City::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'state' => $state, 'status' => 'active']
            );
        }
    }
}
