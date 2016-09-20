<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Gbrock\Table\Traits\Sortable;
use App\Organization;
use App\City;

class Offer extends Model
{
	use Sortable;

    protected $fillable = ['organization_id', 'description', 'image', 'date_start', 'date_end'];
    protected $sortable = ['organization_id', 'date_start', 'date_end'];

    protected $dates = ['date_start', 'date_end'];

    public function organization() {
    	return $this->belongsTo(Organization::class);
    }

    public function cities() {
    	return $this->belongsToMany(City::class, 'offer_city', 'offer_id', 'city_id');
    }
}
