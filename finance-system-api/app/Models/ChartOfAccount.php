<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $fillable = ['code', 'name', 'coa_category_id'];
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(CoaCategory::class, 'coa_category_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'coa_id');
    }
}
