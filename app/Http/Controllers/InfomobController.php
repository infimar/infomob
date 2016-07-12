<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use App\City;
use View;

class InfomobController extends Controller
{
    protected $cityId;
    protected $city;

	public function __construct(Request $request)
    {
    	if ($request->session()->has('city_id'))
        {
            $this->cityId = $request->session()->get('city_id', 0);
        }
        else
        {
            $this->cityId = City::first()->id;
        }        

        // TODO: handle exception - try/catch?
        $this->city = City::findOrFail($this->cityId);
        View::share('chosenCity', $this->city);
    }
}