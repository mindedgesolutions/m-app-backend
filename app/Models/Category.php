<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
        'library'
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id', 'id');
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, SubCategory::class, 'category_id', 'sub_category_id', 'id', 'id');
    }
}
