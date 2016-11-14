<?php

namespace App;

use App\Branch;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    public function branch()
    {
    	return $this->belongsTo(Branch::class);
    }
}
