<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
    	"organization_id",
    	"name", "type", "description", 
    	"raion_id", "address", "post_index",
    	"email", "hits", "lat", "lng"
	];
}
