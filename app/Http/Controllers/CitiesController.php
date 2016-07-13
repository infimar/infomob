<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\City;
use JavaScript;
use Flash;
use DB;

class CitiesController extends InfomobController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cities = City::correct()->orderBy("order", "ASC")->get();

        JavaScript::put(['activeLink' => 'cities_index']);
        return view('cities.admin.index', compact("cities"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        JavaScript::put(['activeLink' => 'cities_create']);
        return view('cities.admin.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $name = $request->input("name");

        if (empty($name))
        {
            Flash::error('Ошибка: укажите название города');
            return redirect()->back();
        }

        // check for slug
        $existingCity = City::where("name", $name)->first();
        
        if ($existingCity !== null)
        {
            Flash::error('Ошибка: такой город уже существует');
            return redirect()->back();
        }

        $city = City::create([
            'name' => $name,
            'status' => $input["status"],
        ]);

        flash()->success("Город добавлен");
        return redirect()->action('CitiesController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try
        {
            $city = City::findOrFail($id);

            JavaScript::put(['activeLink' => 'cities_edit']);
            return view('cities.admin.edit', compact("city"));
        }
        catch (Exception $e)
        {
            flash()->error('Ошибка редактирования: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $name = $request->input("name");

        if (empty($name))
        {
            Flash::error('Ошибка: укажите название города');
            return redirect()->back();
        }

        try
        {
            $city = City::findOrFail($id);

            // check for name
            $existingCity = City::where("name", $name)->first();
        
	        if ($existingCity !== null && $existingCity->id !== $city->id)
	        {
	            Flash::error('Ошибка: такой город уже существует');
	            return redirect()->back();
	        }

            $city->name = $name;
            $city->status = $input['status'];
            $city->save();

            flash()->success("Город обновлен");
            return redirect()->action('CitiesController@index');
        }
        catch (Exception $e)
        {
            flash()->error('Ошибка обновления: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try 
        {
            $city = City::findOrFail($id);

            // update branches, set their city_id to 0 (NO CITY)
            DB::table('branches')->where('city_id', $city->id)->update([
            	'city_id' => 0
        	]);

            $city->delete();

            flash()->info("Город удален");
            return redirect()->back();
        } 
        catch (Exception $e) 
        {
            flash()->error('Ошибка удаления: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
