<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hit extends Model
{
    protected $fillable = [
    	'branch_id', 'user_id', 'ip_addr', 'agent', 'referer'
    ];
}
