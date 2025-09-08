<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
      'title',
      'description',
      'image',
      'price',
      'category_id'
    ];

    public function category() : HasOne
    {
        return $this->hasOne(Category::class);
    }
}
