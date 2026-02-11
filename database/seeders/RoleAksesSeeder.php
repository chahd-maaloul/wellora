<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoleAkses;

class RoleAksesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = config('roles.ALL_ROLES');

        foreach ($roles as $role) {
            RoleAkses::firstOrCreate([
                'name' => $role
            ]);
        }
    }
}
