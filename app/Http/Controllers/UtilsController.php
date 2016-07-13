<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class UtilsController extends InfomobController
{
    /**
     * Change city
     */
    public function changeCity(Request $request, $cityId)
    {
        $request->session()->set("city_id", $cityId);
        return redirect()->back();
    }

    /**
     * Change category
     */
    public function changeCategory(Request $request, $categoryId)
    {
        $request->session()->set("category_id", $categoryId);
        return redirect()->back();
    }
}
