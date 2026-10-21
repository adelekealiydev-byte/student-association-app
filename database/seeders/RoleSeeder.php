<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Admin', 'Treasurer', 'Secretary', 'Member'] as $name) {
            Role::firstOrCreate(['role_name' => $name]);
        }
    }
}

