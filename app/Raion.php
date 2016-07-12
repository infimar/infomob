<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Raion extends Model
{
    protected $fillable = ['name', 'city_id'];

    public function city()
	{
		return $this->belongsTo(City::class);
	}
}
