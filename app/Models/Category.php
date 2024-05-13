<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    public function products(){//dùng cho phần join dữ liệu
        return $this->hasMany(Product::class, 'category_id', 'id');
        //category_id bên sản phẩm, id là của category
    }
}
