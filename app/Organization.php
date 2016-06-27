<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
<<<<<<< HEAD
    //
=======
    protected $fillable = ["name", "type", "description", "category_id"];

    public function category()
    {
    	return $this->belongsTo(Category::class);
    }
>>>>>>> origin/db_seeder
}
