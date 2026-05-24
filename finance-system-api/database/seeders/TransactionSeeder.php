<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = [
            ['date' => '2022-01-01', 'code' => '401', 'desc' => 'Gaji Di Persuhaan A', 'credit' => 5000000],
            ['date' => '2022-01-02', 'code' => '402', 'desc' => 'Gaji Ketum', 'credit' => 7000000],
            ['date' => '2022-01-10', 'code' => '602', 'desc' => 'Bensin Anak', 'debit' => 25000],
            // ... tambah lebih banyak
        ];

        foreach ($transactions as $item) {
            $coa = ChartOfAccount::where('code', $item['code'])->first();
            Transaction::create([
                'date' => $item['date'],
                'coa_id' => $coa->id,
                'description' => $item['desc'] ?? null,
                'debit' => $item['debit'] ?? 0,
                'credit' => $item['credit'] ?? 0,
            ]);
        }
    }
}
