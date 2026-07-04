<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id'=>1, 'name' => 'Адміністратор'],
            ['id'=>2, 'name' => 'Оператор'],
            ['id'=>3, 'name' => 'Керівник'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['id' => $role['id']],
                $role
            );
        }
    }
}