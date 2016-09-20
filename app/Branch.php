<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
