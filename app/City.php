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
}
