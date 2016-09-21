<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Gbrock\Table\Traits\Sortable;
use App\Organization;

class Subscription extends Model
{
	use Sortable;

    protected $fillable = ['organization_id', 'type', 'year', 'expires_in'];
    protected $sortable = ['organization_id', 'type', 'year'];

    public function organization()
    {
    	return $this->belongsTo(Organization::class);
    }

    public static function types($type = "")
    {
    	$types = [
    		'platinum' => 'PLATINUM',
    		'gold' => 'GOLD',
    		'silver' => 'SILVER'
    	];

    	if (!empty($type)) return $types[$type];

    	return $types;
    }

    public static function points()
    {
        return [
            'platinum' => 100,
            'gold' => 50,
            'silver' => 10,
            'none' => 0,
        ];
    }
}
