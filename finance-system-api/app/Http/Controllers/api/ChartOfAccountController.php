<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChartOfAccountRequest;
use App\Http\Requests\UpdateChartOfAccountRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

/**
 * @group Chart of Accounts
 * 
 * Endpoints untuk manage Chart of Accounts (COA) - list akun detail
 */
class ChartOfAccountController extends Controller
{
    /**
     * List all Chart of Accounts
     * 
     * Menampilkan semua akun dengan kategorinya
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "code": "401",
     *       "name": "Gaji Karyawan",
     *       "coa_category_id": 1,
     *       "category_name": "Salary",
     *       "created_at": "2026-05-22T10:00:00.000000Z",
     *       "updated_at": "2026-05-22T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $accounts = ChartOfAccount::with('category')
            ->orderBy('code')
            ->get();
        return ChartOfAccountResource::collection($accounts);
    }

    /**
     * Create new Chart of Account
     * 
     * Membuat akun baru dalam kategori tertentu
     * 
     * @bodyParam code string required Kode akun unik. Example: "401"
     * @bodyParam name string required Nama akun. Example: "Gaji Karyawan"
     * @bodyParam coa_category_id integer required ID kategori. Example: 1
     * 
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "code": "401",
     *     "name": "Gaji Karyawan",
     *     "coa_category_id": 1,
     *     "category_name": "Salary",
     *     "created_at": "2026-05-22T10:00:00.000000Z",
     *     "updated_at": "2026-05-22T10:00:00.000000Z"
     *   }
     * }
     * 
     * @response 422 {
     *   "message": "The code has already been taken",
     *   "errors": {
     *     "code": ["The code has already been taken"]
     *   }
     * }
     */
    public function store(StoreChartOfAccountRequest $request)
    {
        $account = ChartOfAccount::create($request->validated());
        return new ChartOfAccountResource($account->load('category'));
    }

    /**
     * Show single Chart of Account
     * 
     * @urlParam id integer required ID dari akun
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "code": "401",
     *     "name": "Gaji Karyawan",
     *     "coa_category_id": 1,
     *     "category_name": "Salary",
     *     "created_at": "2026-05-22T10:00:00.000000Z",
     *     "updated_at": "2026-05-22T10:00:00.000000Z"
     *   }
     * }
     */
    public function show(ChartOfAccount $chartOfAccount)
    {
        return new ChartOfAccountResource($chartOfAccount->load('category'));
    }

    /**
     * Update Chart of Account
     * 
     * @urlParam id integer required ID dari akun
     * @bodyParam code string required Kode akun. Example: "401"
     * @bodyParam name string required Nama akun. Example: "Gaji Karyawan Updated"
     * @bodyParam coa_category_id integer required ID kategori. Example: 1
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "code": "401",
     *     "name": "Gaji Karyawan Updated",
     *     "coa_category_id": 1,
     *     "category_name": "Salary",
     *     "created_at": "2026-05-22T10:00:00.000000Z",
     *     "updated_at": "2026-05-24T15:30:00.000000Z"
     *   }
     * }
     */
    public function update(UpdateChartOfAccountRequest $request, ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->update($request->validated());
        return new ChartOfAccountResource($chartOfAccount->load('category'));
    }

    /**
     * Delete Chart of Account
     * 
     * @urlParam id integer required ID dari akun
     * 
     * @response 200 {
     *   "message": "Akun berhasil dihapus"
     * }
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->delete();
        return response()->json(['message' => 'Akun berhasil dihapus']);
    }
}
