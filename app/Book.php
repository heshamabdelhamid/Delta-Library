<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $guarded = [];
    protected $appends = ['image_path'];

    public function Category()
    {   
        return $this->belongsto('App\Category');
    }

    public function getImagePathAttribute()
    {
        return asset('uploads/book_images/' . $this->image);

    }
 
}