<?php

namespace Database\Seeders;

use App\Models\CoaCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoaCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Salary',
            'Other Income',
            'Family Expense',
            'Transport Expense',
            'Meal Expense',
        ];

        foreach ($categories as $name) {
            CoaCategory::firstOrCreate(['name' => $name]);
        }
    }
}
