<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable = [
        'product_id',
        'name'
    ];

    public function products(): HasMany{
        return $this->hasMany(Product::class);
    }
}
