<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Subscription;

class Organization extends Model
{
    protected $fillable = ["name", "type", "description", "order", "status", "notes", "logo"];

    /**
	 * Relationships
	 */

    public function branches()
    {
    	return $this->hasMany(Branch::class);
    }

    public function subscription()
    {
    	return $this->hasOne(Subscription::class);
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
