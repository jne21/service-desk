<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Відділ Електриків'],
            ['name' => 'Ремонтно-будівельний відділ'],
            ['name' => 'Ліфтери'],
            ['name' => 'Благоустрій'],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['name' => $department['name']],
                $department
            );
        }
    }
}