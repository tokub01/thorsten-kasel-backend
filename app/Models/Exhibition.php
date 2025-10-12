<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exhibition extends Model
{
    use HasFactory;

    protected $table="exhibitions";

    protected $fillable = [
        "title",
        "description",
        "text",
        "date",
        "image",
        "isActive"
    ];
}
