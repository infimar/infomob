<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD

class City extends Model
{
    //
=======
use App\Raion;

class City extends Model
{
    protected $fillable = ['name'];

    public function raions()
    {
    	return $this->hasMany(Raion::class);
    }
>>>>>>> origin/db_seeder
}
