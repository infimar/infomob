<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use App\City;
use App\Category;
use View;
use JavaScript;

class InfomobController extends Controller
{
    protected $cityId;
    protected $city;

    protected $categoryId;
    protected $category;

    protected $perPage = 25;

	public function __construct(Request $request)
    {
        // $request->session()->forget('city_id');
        // $request->session()->forget('category_id');

    	if ($request->session()->has('city_id'))
        {
            $this->cityId = $request->session()->get('city_id', 0);
        }
        else
        {
            $this->cityId = City::correct()->first()->id;
        }

        if ($request->session()->has('category_id'))
        {
            $this->categoryId = $request->session()->get('category_id', 0);
        }
        else
        {
            $this->categoryId = Category::allLeaves()->first()->id;
        }

        // TODO: handle exception - try/catch?
        $this->city = City::findOrFail($this->cityId);
        $this->category = Category::findOrFail($this->categoryId);

        View::share('chosenCity', $this->city);
        View::share('chosenCategory', $this->category);
        View::share('prevUrl', url()->previous());

        JavaScript::put(["chosenCity" => $this->city, "chosenCategory" => $this->category]);
    }
}