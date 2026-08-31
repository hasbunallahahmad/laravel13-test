<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => 'admin@dynamiccms.test',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Super Administrator',
                'password' => Hash::make('ChangeThisPassword123!'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles('super-admin');
    }
}
