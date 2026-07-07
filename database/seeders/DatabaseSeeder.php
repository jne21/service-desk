<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'vve11es@gmail.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('pwd'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'ekutuzov@itera-research.com'],
            [
                'name' => 'operator',
                'password' => Hash::make('pwd'),
            ]
        );

        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            TicketStatusSeeder::class,
            TicketImportStatusSeeder::class,
        ]);

    }
}
