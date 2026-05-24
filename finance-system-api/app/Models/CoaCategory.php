<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoaCategory extends Model
{
    protected $fillable = ['name'];
    use HasFactory;

    public function chartOfAccounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'coa_category_id');
    }
}
