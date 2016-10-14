<?php

namespace App;

use App\Branch;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    protected $fillable = [
    	"branch_id", "type", "code_country", 
    	"code_operator", "number", "contact_person"
	];

	public function branch()
	{
		return $this->belongsTo(Branch::class);
	}
}