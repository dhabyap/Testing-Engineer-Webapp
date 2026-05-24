<?php

namespace App\Http\Controllers\api;

use App\Exports\ProfitLossExport;
use App\Http\Controllers\Controller;
use App\Models\CoaCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @group Reports
 * 
 * Endpoints untuk generate laporan Profit & Loss
 */
class ProfitLossController extends Controller
{
    /**
     * Get Profit & Loss Report
     * 
     * Generate laporan laba rugi untuk periode bulan tertentu.
     * Format period: YYYY-MM (contoh: 2026-05 untuk Mei 2026)
     * 
     * @urlParam year_month string required Format YYYY-MM. Example: "2026-05"
     * 
     * @response 200 {
     *   "period": "2026-05",
     *   "report": [
     *     {
     *       "category_name": "Salary",
     *       "amount": 12000000,
     *       "type": "income"
     *     },
     *     {
     *       "category_name": "Transport Expense",
     *       "amount": 250000,
     *       "type": "expense"
     *     }
     *   ],
     *   "total_income": 12000000,
     *   "total_expense": 250000,
     *   "net_income": 11750000
     * }
     * 
     * @response 400 {
     *   "error": "Format tidak valid"
     * }
     */
    public function show($year_month)
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $year_month)) {
            return response()->json(['error' => 'Format tidak valid'], 400);
        }

        $startDate = Carbon::createFromFormat('Y-m-d', $year_month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all categories
        $categories = CoaCategory::with('chartOfAccounts.transactions')
            ->get();

        $report = [];
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($categories as $category) {
            // Calculate total credit (income) dan debit (expense) untuk kategori ini
            $income = 0;
            $expense = 0;

            foreach ($category->chartOfAccounts as $account) {
                $transactions = $account->transactions()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();

                foreach ($transactions as $txn) {
                    $income += $txn->credit;
                    $expense += $txn->debit;
                }
            }

            if ($income > 0 || $expense > 0) {
                $report[] = [
                    'category_name' => $category->name,
                    'amount' => $income > 0 ? $income : $expense,
                    'type' => $income > 0 ? 'income' : 'expense',
                ];

                if ($income > 0) {
                    $totalIncome += $income;
                } else {
                    $totalExpense += $expense;
                }
            }
        }

        $netIncome = $totalIncome - $totalExpense;

        return response()->json([
            'period' => $year_month,
            'report' => $report,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
        ]);
    }

    /**
     * Export single month (backward compatible)
     * GET /profit-loss-export/{year_month}
     */
    public function exportSingle($year_month)
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $year_month)) {
            return response()->json(['error' => 'Format tidak valid'], 400);
        }

        return Excel::download(
            new ProfitLossExport($year_month, $year_month),
            'profit-loss-' . $year_month . '.xlsx'
        );
    }

    /**
     * Export Profit & Loss Report to Excel (multi-period)
     * @queryParam from string required Format YYYY-MM. Example: "2026-01"
     * @queryParam to string required Format YYYY-MM. Example: "2026-03"
     */
    public function export(Request $request)
    {
        $from = $request->query('from');
        $to   = $request->query('to');

        if (!preg_match('/^\d{4}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}$/', $to)) {
            return response()->json(['error' => 'Format tidak valid. Gunakan ?from=YYYY-MM&to=YYYY-MM'], 400);
        }

        return Excel::download(
            new ProfitLossExport($from, $to),
            'profit-loss-' . $from . '-to-' . $to . '.xlsx'
        );
    }
}
