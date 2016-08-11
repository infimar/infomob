<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Raion;

class City extends Model
{
    protected $fillable = ['name'];

    public function raions()
    {
    	return $this->hasMany(Raion::class);
    }

    public static function dropdown($all = false)
    {
    	$cities = City::orderBy("order", "ASC");

    	if (!$all)
    		$cities->correct();

    	return $cities->lists("name", "id");
    }

    public function scopePublished($query)
    {
        $query->where("status", "published");
    }

    public function scopeCorrect($query)
    {
        $query->where("id", "!=", 999999);
    }
}
