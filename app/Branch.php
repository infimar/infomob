<?php

namespace App;

use App\Hit;
use App\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Branch extends Model
{
	use \Conner\Tagging\Taggable;
	
    protected $fillable = [
    	"organization_id", "status",
    	"name", "type", "description", 
    	"city_id", "address", "post_index",
    	"email", "hits", "lat", "lng", "working_hours",
    	"pricingfile"
	];


	/**
	 * Relationships
	 */
	
	public function city() {
		return $this->belongsTo(City::class);
	}
	
	public function photos() {
		return $this->hasMany(Photo::class);
	}
	
	public function videos() {
		return $this->hasMany(Video::class);
	}

	public function phones() {
		return $this->hasMany(Phone::class);
	}
	
	public function socials() {
		return $this->hasMany(Social::class);
	}
	
	public function organization() {
		return $this->belongsTo(Organization::class);
	}
	
	public function categories()
	{
		return $this->belongsToMany('App\Category', 'branch_category', 'branch_id', 'category_id');
	}

	public function hits()
	{
		return $this->hasMany(Hit::class);
	}


	/**
	 * Scopes
	 */
	
	public function scopePublished($query)
	{
		return $query->where("status", "published");
	}

	public function scopeDraft($query)
	{
		return $query->where("status", "draft");
	}

	public function scopePrivate($query)
	{
		return $query->where("status", "private");
	}

	public function scopeTrashed($query)
	{
		return $query->where("status", "trashed");
	}

	public function scopeArchived($query)
	{
		return $query->where("status", "archived");
	}

	/**
	 * Methods
	 */
	public function addHit(Request $request, $agent, $referer)
	{
		$hit = new Hit([
			'branch_id' => $this->id,
			'user_id' 	=> 1,				// user id 
			'ip_addr' 	=> $request->ip(), 
			'agent' 	=> $agent, 
			'referer' 	=> $referer
		]);

		$this->hits()->save($hit);
	}
}
