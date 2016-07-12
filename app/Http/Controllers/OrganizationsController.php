<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Organization;
use App\Category;
use App\Branch;
use App\Phone;
use App\Social;
use JavaScript;
use Flash;
use DB;

class OrganizationsController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	$city = $this->city;

        $organizations = Organization::orderBy("id", "DESC")
        	->whereHas("branches", function($query) use ($city) 
        	{
        		$query->where('city_id', $city->id);
        	})
        	->get();

        JavaScript::put(['activeLink' => 'organizations_index']);
        return view('organizations.admin.index', compact("organizations"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        JavaScript::put(['activeLink' => 'organizations_create']);
        $backUrl = url()->previous();

        return view('organizations.admin.create', compact("backUrl"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // TODO: validation
        // dd($request->all());
        $input = $request->all();
        DB::beginTransaction();

        try 
        {
            // organization
            $organization = Organization::create([
                'name' => $input['name'],
                'description' => $input['description'],
                'status' => $input['status'],
            ]);
            // dd($organization);

            // branch
            // lat, lng
            $lat = (empty($input['branch_lat'])) ? "0.00" : $input['branch_lat'];
            $lng = (empty($input['branch_lng'])) ? "0.00" : $input['branch_lng'];

            // branch info
            $branch = Branch::create([
                'organization_id' => $organization->id,
                'city_id' => $input['branch_cityId'],
                'name' => $input['name'],
                'description' => $input['description'],
                'address' => $input['branch_address'],
                'post_index' => $input['branch_postIndex'],
                'email' => $input['branch_email'],
                'lat' => $lat,
                'lng' => $lng,
                'working_hours' => $input['branch_workingHours'],
                'status' => $input['branch_status']
            ]);
            // dd($branch);

            // set branch category
            $category = Category::findOrFail($input['branch_categoryId']);
            $success = DB::table('branch_category')->insert([
                'branch_id' => $branch->id,
                'category_id' => $category->id
            ]);
            // dd($success);
            
            // branch phones
            if (isset($input['branch_phones']))
            {
                foreach ($input['branch_phones'] as $phone)
                {
                    $phone = Phone::create([
                        'branch_id' => $branch->id,
                        'type' => $phone['type'],
                        'code_country' => $phone['code_country'],
                        'code_operator' => $phone['code_operator'],
                        'number' => $phone['number'],
                        'contact_person' => $phone['contact_person'],
                    ]);
                    // dd($phone);
                }
            }

            // branch socials
            if (isset($input['branch_socials']))
            {
                foreach ($input['branch_socials'] as $social)
                {
                    $social = Social::create([
                        'branch_id' => $branch->id,
                        'type' => $social['type'],
                        'name' => $social['name'],
                        'contact_person' => $social['contact_person'],
                    ]);
                    // dd($social);
                }
            }

            DB::commit();
        } 
        catch (Exception $e) 
        {
            DB::rollBack();
            flash()->error('Ошибка: ' . $e->getMessage());
            return redirect()->back()->withInput();
        } 
        
        flash()->success("Организация успешно добавлена");
        return redirect()->action('OrganizationsController@index');
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
    public function edit(Request $request, $id)
    {
        $backUrl = url()->previous();
        $pickedCityId = 0;

        if ($request->has('city_id'))
        {
            $pickedCityId = $request->input('city_id');
        }

        try
        {
            $organization = Organization::
                with(['branches' => function($q) use ($pickedCityId) { 
                    if ($pickedCityId != 0)
                        $q->where("city_id", $pickedCityId);

                    $q->orderBy("type", "DESC"); 
                }, "branches.city", "branches.categories"])
                ->findOrFail($id);

            JavaScript::put(['activeLink' => 'organizations_edit']);
            return view('organizations.admin.edit', compact("organization", "backUrl", 'pickedCityId'));
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
        // TODO: validation
        // dd($request->all());
        $input = $request->all();

        try
        {
            $organization = Organization::findOrFail($id);
            $organization->name = $input['name'];
            $organization->description = $input['description'];
            $organization->status = $input['status'];
            $organization->save();

            flash()->success("Организация успешно обновлена");
            return redirect()->action('OrganizationsController@index');
        }
        catch (Exception $e)
        {
            flash()->error("Ошибка: " . $e->getMessage);
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
            $organization = Organization::with(['branches', 'branches.phones', 'branches.photos', 'branches.socials'])->findOrFail($id);

            foreach ($organization->branches as $branch)
            {
                foreach ($branch->phones as $key => $phone) 
                {
                    $phone->delete();
                }

                foreach ($branch->socials as $key => $social) 
                {
                    $social->delete();
                }

                foreach ($branch->photos as $key => $photo) 
                {
                    $photo->delete();
                }

                $branch->delete();
            }

            $organization->delete();

            flash()->success("Организация удалена");
            return redirect()->back();
        } 
        catch (Exception $e) 
        {
            flash()->error('Ошибка удаления: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
