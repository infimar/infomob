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
use File;

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
        $category = $this->category;

        $organizations = Organization::orderBy("id", "DESC")
        	->whereHas("branches", function($query) use ($city, $category) 
        	{
        		$query->where('city_id', $city->id);
                $query->whereHas('categories', function ($q) use ($category) {
                    $q->where("category_id", $category->id);
                });
        	})
        	->get();

        $ids = [];
        foreach ($organizations as $organization)
        {
            $ids[] = $organization->id;
        }

        $toptens = DB::table('toptens')
            ->where("city_id", $city->id)
            ->where('category_id', $category->id)
            ->whereIn('organization_id', $ids)
            ->get();
        
        $topten_map = [];
        foreach ($toptens as $key => $topten) 
        {
            $topten_map[$topten->organization_id] = true;
        }

        $count = [];
        $catIds = [];
        $categories = Category::where("parent_id", "!=", null)->get();
        foreach ($categories as $category)
        {
            $catIds[] = $category->id;
            $count[$category->id] = 0;
        }
        // dd($catIds);

        $branches = Branch::where("city_id", $city->id)
            ->with('categories')
            ->whereHas('categories', function($q) use ($catIds)
            {
                $q->whereIn("category_id", $catIds);
            })->get(['id', 'city_id']);
        
        foreach ($branches as $key => $branch) 
        {
            foreach ($branch->categories as $category)
            {
                if (isset($count[$category->id]))
                {
                    $count[$category->id] += 1;
                }
            }
        }
        // dd($count);
        
        JavaScript::put(['activeLink' => 'organizations_index']);
        return view('organizations.admin.index', compact("organizations", "topten_map", 'count'));
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
            $categoryIds = [];
            foreach ($input['branch_categoryIds'] as $key => $catId) 
            {
                $categoryIds[] = $catId;
            }

            $categories = Category::whereIn("id", $categoryIds)->get();
            foreach ($categories as $category) 
            {
                $success = DB::table('branch_category')->insert([
                    'branch_id' => $branch->id,
                    'category_id' => $category->id
                ]);
                // dd($success);
            }
            
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

            // tags
            $tags = explode("|", $input['branch_tags']);
            // dd($tags);
            foreach ($tags as $key => $tag) 
            {
                if (empty($tag)) continue;
                $branch->tag($tag);
            }
            // dd($branch->tagNames());

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
        $pickedCategoryId = 0;

        if ($request->has('city_id'))
        {
            $pickedCityId = $request->input('city_id');
        }

        if ($request->has('category_id'))
        {
            $pickedCategoryId = $request->input('category_id');
        }

        try
        {
            $organization = Organization::
                with(['branches' => function($q) use ($pickedCityId, $pickedCategoryId) { 
                    if ($pickedCityId != 0)
                        $q->where("city_id", $pickedCityId);

                    if ($pickedCategoryId != 0)
                        $q->whereHas("categories", function ($query) use ($pickedCategoryId) {
                            $query->where("id", $pickedCategoryId);
                        });
                }, "branches.city", "branches.categories", "branches.photos"])->findOrFail($id);

            foreach ($organization->branches as $branch)
            {
                $categoryLabel = "";

                $length = count($branch->categories);
                foreach ($branch->categories as $key => $category)
                {
                    $categoryLabel .= $category->name;
                    if ($key + 1 < $length) $categoryLabel .= " | ";
                }

                $branch->categoryLabel = $categoryLabel;
            }

            JavaScript::put([
                'activeLink' => 'organizations_edit',
                'pickedCityId' => $pickedCityId,
                'pickedCategoryId' => $pickedCategoryId
            ]);

            return view('organizations.admin.edit', compact("organization", "backUrl", 'pickedCityId', 'pickedCategoryId'));
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
            // return redirect()->back();
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
                    File::delete(public_path() . "/images/photos/" . $photo->path);
                    $photo->delete();
                }

                $branch->delete();
            }

            $organization->delete();

            flash()->info("Организация удалена");
            return redirect()->back();
        } 
        catch (Exception $e) 
        {
            flash()->error('Ошибка удаления: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function topTen()
    {
        $toptens = DB::table('toptens')
            ->join('organizations', 'toptens.organization_id', '=', 'organizations.id')
            ->where('city_id', $this->city->id)
            ->where('category_id', $this->category->id)
            ->orderBy('toptens.order')
            ->get(['city_id', 'category_id', 'organization_id', 'toptens.order', 'organizations.name']);
        // dd($toptens);

        JavaScript::put(['activeLink' => 'organizations_topten']);
        return view('organizations.admin.topten', compact('toptens'));
    }
}
