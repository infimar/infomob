<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    protected $fillable = [
    	"branch_id", "type", "code_country", 
    	"code_operator", "number", "contact_person"
	];
}