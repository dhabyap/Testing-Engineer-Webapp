<?php

namespace App\Exports;

use App\Models\CoaCategory;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProfitLossExport implements WithEvents
{
    protected string $from;
    protected string $to;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    /** Hasilkan semua tanggal dari from s/d to */
    private function getDates(): array
    {
        $dates = [];
        $current = Carbon::createFromFormat('Y-m', $this->from)->startOfMonth();
        $end     = Carbon::createFromFormat('Y-m', $this->to)->endOfMonth();

        while ($current->lte($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dates = $this->getDates();
                $numDates = count($dates);
                $lastCol  = $this->colLetter($numDates); // B=1 date, C=2 dates, ...

                // --- Kumpulkan data per kategori per tanggal ---
                $categories = CoaCategory::with('chartOfAccounts.transactions')->get();

                $incomeRows  = [];
                $expenseRows = [];
                $totalIncome  = array_fill_keys($dates, 0);
                $totalExpense = array_fill_keys($dates, 0);

                foreach ($categories as $category) {
                    $amounts = array_fill_keys($dates, 0);
                    $type    = null;

                    foreach ($category->chartOfAccounts as $account) {
                        $txns = $account->transactions()
                            ->whereBetween('date', [$dates[0], $dates[count($dates) - 1]])
                            ->get();

                        foreach ($txns as $txn) {
                            $d = $txn->date; // format Y-m-d
                            if (isset($amounts[$d])) {
                                if ($txn->credit > 0) {
                                    $amounts[$d] += $txn->credit;
                                    $type = 'income';
                                } elseif ($txn->debit > 0) {
                                    $amounts[$d] += $txn->debit;
                                    $type = 'expense';
                                }
                            }
                        }
                    }

                    if ($type === 'income') {
                        $incomeRows[] = ['name' => $category->name, 'amounts' => $amounts];
                        foreach ($dates as $d) $totalIncome[$d] += $amounts[$d];
                    } elseif ($type === 'expense') {
                        $expenseRows[] = ['name' => $category->name, 'amounts' => $amounts];
                        foreach ($dates as $d) $totalExpense[$d] += $amounts[$d];
                    }
                }

                $netIncome = [];
                foreach ($dates as $d) {
                    $netIncome[$d] = $totalIncome[$d] - $totalExpense[$d];
                }

                // --- Tulis ke sheet ---
                // Row 1: Judul
                $sheet->setCellValue('A1', 'Laporan Profit/Loss');
                if ($numDates > 1) {
                    $sheet->mergeCells('A1:' . $lastCol . '1');
                }
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

                // Row 2: tanggal header (format d/m/Y)
                foreach ($dates as $i => $d) {
                    $col = $this->colLetter($i + 1);
                    $sheet->setCellValue($col . '2', Carbon::parse($d)->format('d/m/Y'));
                    $sheet->getStyle($col . '2')->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }

                // Row 3: "Category" + "Amount" per tanggal
                $sheet->setCellValue('A3', 'Category');
                $sheet->getStyle('A3')->getFont()->setBold(true);
                foreach ($dates as $i => $d) {
                    $col = $this->colLetter($i + 1);
                    $sheet->setCellValue($col . '3', 'Amount');
                    $sheet->getStyle($col . '3')->getFont()->setBold(true);
                    $sheet->getStyle($col . '3')->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }

                $row = 4;

                // Income rows
                foreach ($incomeRows as $item) {
                    $sheet->setCellValue('A' . $row, $item['name']);
                    foreach ($dates as $i => $d) {
                        $sheet->setCellValue($this->colLetter($i + 1) . $row, $item['amounts'][$d] ?: '');
                    }
                    $row++;
                }

                // Total Income
                $sheet->setCellValue('A' . $row, 'Total Income');
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                foreach ($dates as $i => $d) {
                    $sheet->setCellValue($this->colLetter($i + 1) . $row, $totalIncome[$d]);
                    $sheet->getStyle($this->colLetter($i + 1) . $row)->getFont()->setBold(true);
                }
                $row++;

                // Expense rows
                foreach ($expenseRows as $item) {
                    $sheet->setCellValue('A' . $row, $item['name']);
                    foreach ($dates as $i => $d) {
                        $sheet->setCellValue($this->colLetter($i + 1) . $row, $item['amounts'][$d] ?: '');
                    }
                    $row++;
                }

                // Total Expense
                $sheet->setCellValue('A' . $row, 'Total Expense');
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                foreach ($dates as $i => $d) {
                    $sheet->setCellValue($this->colLetter($i + 1) . $row, $totalExpense[$d]);
                    $sheet->getStyle($this->colLetter($i + 1) . $row)->getFont()->setBold(true);
                }
                $row++;

                // Net Income
                $sheet->setCellValue('A' . $row, 'Net Income');
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                foreach ($dates as $i => $d) {
                    $sheet->setCellValue($this->colLetter($i + 1) . $row, $netIncome[$d]);
                    $sheet->getStyle($this->colLetter($i + 1) . $row)->getFont()->setBold(true);
                }

                // --- Lebar kolom ---
                $sheet->getColumnDimension('A')->setWidth(25);
                foreach ($dates as $i => $d) {
                    $sheet->getColumnDimension($this->colLetter($i + 1))->setWidth(14);
                }

                // Format angka
                $sheet->getStyle($this->colLetter(1) . '4:' . $lastCol . $row)
                    ->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }

    private function colLetter(int $index): string
    {
        // index 1-based: 1=B, 2=C, ... (A adalah Category)
        $index++; // A=Category, jadi date col mulai dari B (index+1)
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index  = intdiv($index, 26);
        }
        return $letter;
    }
}
