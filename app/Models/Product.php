<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Product extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable = [
      'title',
      'description',
      'image',
      'price',
      'category_id',
      'isActive'
    ];

    public function category() : HasOne
    {
        return $this->hasOne(Category::class);
    }
}
