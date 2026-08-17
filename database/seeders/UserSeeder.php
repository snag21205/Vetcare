<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin System',      'email' => 'admin@vetcare.local',     'role' => 'admin'],
            ['name' => 'Nguyễn Lễ Tân',    'email' => 'letan@vetcare.local',      'role' => 'receptionist'],
            ['name' => 'BS. Trần Văn Khoa', 'email' => 'bacsi@vetcare.local',     'role' => 'vet'],
            ['name' => 'Lê Kế Toán',        'email' => 'ketoan@vetcare.local',    'role' => 'accountant'],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);
            $data['password'] = Hash::make('password');

            $user = User::firstOrCreate(['email' => $data['email']], $data);
            $user->assignRole($role);
        }

        $this->command->info('Users created! Password: "password"');
    }
}
