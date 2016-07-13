<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    protected $fillable = [
    	"branch_id", "type", "name", "contact_person"
	];
}