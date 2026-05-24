<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\CoaCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['code' => '401', 'name' => 'Gaji Karyawan', 'category' => 'Salary'],
            ['code' => '402', 'name' => 'Gaji Ketua MPR', 'category' => 'Salary'],
            ['code' => '403', 'name' => 'Profit Trading', 'category' => 'Other Income'],
            ['code' => '601', 'name' => 'Biaya Sekolah', 'category' => 'Family Expense'],
            ['code' => '602', 'name' => 'Bensin', 'category' => 'Transport Expense'],
            ['code' => '603', 'name' => 'Parkir', 'category' => 'Transport Expense'],
            ['code' => '604', 'name' => 'Makan Siang', 'category' => 'Meal Expense'],
            ['code' => '605', 'name' => 'Makanan Pokok Bulanan', 'category' => 'Meal Expense'],
        ];

        foreach ($data as $item) {
            $category = CoaCategory::where('name', $item['category'])->first();
            ChartOfAccount::firstOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'coa_category_id' => $category->id,
                ]
            );
        }
    }
}
