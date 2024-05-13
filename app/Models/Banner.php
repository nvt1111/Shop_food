<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'link', 'image', 'description', 'prioty', 'position' ];

    public function scopeGetBanner($q, $pos = 'top-banner'){
        $q = $q->where('position', $pos)->where('status', 1)->orderBy('prioty', 'ASC');
// $q là $query
        return $q;
    }
}
