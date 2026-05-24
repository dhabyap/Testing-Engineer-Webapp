<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCoaCategoryRequest;
use App\Http\Requests\UpdateCoaCategoryRequest;
use App\Http\Resources\CoaCategoryResource;
use App\Models\CoaCategory;
use Illuminate\Http\Request;

/**
 * @group COA Categories
 * 
 * Endpoints untuk manage kategori Chart of Accounts
 */
class CoaCategoryController extends Controller
{
    /**
     * List all COA categories
     * 
     * Menampilkan semua kategori COA yang tersedia
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Salary",
     *       "created_at": "2026-05-22T10:00:00.000000Z",
     *       "updated_at": "2026-05-22T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $categories = CoaCategory::orderBy('name')->get();
        return CoaCategoryResource::collection($categories);
    }

    /**
     * Create new COA category
     * 
     * Membuat kategori COA baru
     * 
     * @bodyParam name string required Nama kategori, harus unique. Example: "Salary"
     * 
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "name": "Salary",
     *     "created_at": "2026-05-22T10:00:00.000000Z",
     *     "updated_at": "2026-05-22T10:00:00.000000Z"
     *   }
     * }
     * 
     * @response 422 {
     *   "message": "The name has already been taken",
     *   "errors": {
     *     "name": ["The name has already been taken"]
     *   }
     * }
     */
    public function store(StoreCoaCategoryRequest $request)
    {
        $category = CoaCategory::create($request->validated());
        return new CoaCategoryResource($category);
    }

    /**
     * Show single COA category
     * 
     * Get detail kategori berdasarkan ID
     * 
     * @urlParam id integer required ID dari kategori
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Salary",
     *     "created_at": "2026-05-22T10:00:00.000000Z",
     *     "updated_at": "2026-05-22T10:00:00.000000Z"
     *   }
     * }
     * 
     * @response 404 {
     *   "message": "No query results"
     * }
     */
    public function show(CoaCategory $coaCategory)
    {
        return new CoaCategoryResource($coaCategory);
    }

    /**
     * Update COA category
     * 
     * Update kategori yang sudah ada
     * 
     * @urlParam id integer required ID dari kategori
     * @bodyParam name string required Nama kategori yang baru. Example: "Salary Updated"
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Salary Updated",
     *     "created_at": "2026-05-22T10:00:00.000000Z",
     *     "updated_at": "2026-05-24T15:30:00.000000Z"
     *   }
     * }
     */
    public function update(UpdateCoaCategoryRequest $request, CoaCategory $coaCategory)
    {
        $coaCategory->update($request->validated());
        return new CoaCategoryResource($coaCategory);
    }

    /**
     * Delete COA category
     * 
     * Hapus kategori beserta semua akun terkait (cascade delete)
     * 
     * @urlParam id integer required ID dari kategori
     * 
     * @response 200 {
     *   "message": "Kategori berhasil dihapus"
     * }
     */
    public function destroy(CoaCategory $coaCategory)
    {
        $coaCategory->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}
