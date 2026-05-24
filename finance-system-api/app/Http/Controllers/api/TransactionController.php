<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

/**
 * @group Transactions
 * 
 * Endpoints untuk manage pencatatan transaksi keuangan
 */
class TransactionController extends Controller
{
    /**
     * List all transactions
     * 
     * Menampilkan semua transaksi yang tercatat
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "date": "2026-05-22",
     *       "coa_id": 1,
     *       "coa_code": "401",
     *       "coa_name": "Gaji Karyawan",
     *       "description": "Gaji Di Perusahaan A",
     *       "debit": 0,
     *       "credit": 5000000,
     *       "created_at": "2026-05-22T10:00:00.000000Z",
     *       "updated_at": "2026-05-22T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $transactions = Transaction::with('chartOfAccount')
            ->orderBy('date', 'desc')
            ->get();
        return TransactionResource::collection($transactions);
    }

    /**
     * Create new transaction
     * 
     * Membuat pencatatan transaksi baru. Bisa debit atau credit, tapi tidak boleh keduanya kosong
     * 
     * @bodyParam date string required Tanggal transaksi format YYYY-MM-DD. Example: "2026-05-22"
     * @bodyParam coa_id integer required ID akun yang ditransaksikan. Example: 1
     * @bodyParam description string Deskripsi transaksi (optional). Example: "Gaji Bulan Mei"
     * @bodyParam debit numeric Nominal pengeluaran/beban (Rp). Example: 0
     * @bodyParam credit numeric Nominal pemasukan/pendapatan (Rp). Example: 5000000
     * 
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "date": "2026-05-22",
     *     "coa_id": 1,
     *     "coa_code": "401",
     *     "coa_name": "Gaji Karyawan",
     *     "description": "Gaji Bulan Mei",
     *     "debit": 0,
     *     "credit": 5000000,
     *     "created_at": "2026-05-22T10:00:00.000000Z",
     *     "updated_at": "2026-05-22T10:00:00.000000Z"
     *   }
     * }
     * 
     * @response 422 {
     *   "message": "The given data was invalid",
     *   "errors": {
     *     "debit": ["Debit or Credit harus diisi salah satu"]
     *   }
     * }
     */
    public function store(StoreTransactionRequest $request)
    {
        $transaction = Transaction::create($request->validated());
        return new TransactionResource($transaction->load('chartOfAccount'));
    }

    /**
     * Show single transaction
     * 
     * @urlParam id integer required ID dari transaksi
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "date": "2026-05-22",
     *     "coa_id": 1,
     *     "coa_code": "401",
     *     "coa_name": "Gaji Karyawan",
     *     "description": "Gaji Bulan Mei",
     *     "debit": 0,
     *     "credit": 5000000,
     *     "created_at": "2026-05-22T10:00:00.000000Z",
     *     "updated_at": "2026-05-22T10:00:00.000000Z"
     *   }
     * }
     */
    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction->load('chartOfAccount'));
    }

    /**
     * Update transaction
     * 
     * @urlParam id integer required ID dari transaksi
     * @bodyParam date string required Tanggal transaksi. Example: "2026-05-22"
     * @bodyParam coa_id integer required ID akun. Example: 1
     * @bodyParam description string Deskripsi transaksi. Example: "Gaji Bulan Mei Updated"
     * @bodyParam debit numeric Nominal debit. Example: 0
     * @bodyParam credit numeric Nominal credit. Example: 5000000
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "date": "2026-05-22",
     *     "coa_id": 1,
     *     "coa_code": "401",
     *     "coa_name": "Gaji Karyawan",
     *     "description": "Gaji Bulan Mei Updated",
     *     "debit": 0,
     *     "credit": 5000000,
     *     "created_at": "2026-05-22T10:00:00.000000Z",
     *     "updated_at": "2026-05-24T15:30:00.000000Z"
     *   }
     * }
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $transaction->update($request->validated());
        return new TransactionResource($transaction->load('chartOfAccount'));
    }

    /**
     * Delete transaction
     * 
     * @urlParam id integer required ID dari transaksi
     * 
     * @response 200 {
     *   "message": "Transaksi berhasil dihapus"
     * }
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message' => 'Transaksi berhasil dihapus']);
    }
}
