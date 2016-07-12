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

class BranchesController extends AdminController
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($organizationId)
    {
    	try 
    	{
    		$organization = Organization::findOrFail($organizationId);
        	$backUrl = url()->previous();
    		
    		JavaScript::put(['activeLink' => 'branches_create']);
        	return view('branches.admin.create', compact("organization", "backUrl"));
    	} 
    	catch (Exception $e) 
    	{
    		flash()->error('Ошибка: ' . $e->getMessage());
            return redirect()->back();
    	}
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $organizationId)
    {
        // TODO: validation
        // dd($request->all());
        $input = $request->all();
        DB::beginTransaction();

        try 
        {
            // organization
            $organization = Organization::findOrFail($organizationId);
            // dd($organization);

            // branch
            // lat, lng
            $lat = (empty($input['branch_lat'])) ? "0.00" : $input['branch_lat'];
            $lng = (empty($input['branch_lng'])) ? "0.00" : $input['branch_lng'];

            // branch info
            $branch = Branch::create([
                'organization_id' => $organization->id,
                'city_id' => $input['branch_cityId'],
                'name' => $input['branch_name'],
                'description' => $input['branch_description'],
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
        
        flash()->success("Филиал успешно добавлен");
        return redirect()->action('OrganizationsController@edit', ['organizationId' => $organization->id]);
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

        try
        {
            $branch = Branch::
                with(['organization', 'phones', 'socials', 'photos', 'city', 'categories'])
                ->findOrFail($id);

            JavaScript::put(['activeLink' => 'branches_edit']);
            return view('branches.admin.edit', compact("branch", "backUrl"));
        }
        catch (Exception $e)
        {
            flash()->error('Ошибка редактирования: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
