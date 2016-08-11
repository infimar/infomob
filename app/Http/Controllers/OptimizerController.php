<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use View;
use DB;
use Cache;
use App\Category;
use App\City;
use App\Organization;
use App\Branch;

class OptimizerController extends InfomobController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        // $this->middleware('auth');
        parent::__construct($request);

        // city, cityId
        if ($this->cityId == 0)
        {
            $this->city = City::correct()->orderBy('order')->first();
            $this->cityId = $this->city->id;

            View::share('chosenCity', $this->city);
            JavaScript::put(["chosenCity" => $this->city, "chosenCategory" => $this->category]);
        }

        // query
        $this->query = "";
        if ($request->has('query'))
        {
            $this->query = $request->input('query');
        }
        View::share('query', $this->query);
    }

    public function categories()
    {
    	DB::table('view_categories')->delete();
    	$cities = City::published()->correct()->get();

    	foreach ($cities as $city)
    	{
    		$categoriesDB = Category::where("status", "published")
	            ->where("parent_id", null)
	            ->orderBy("name", "ASC")
	            ->get();
	        // dd($categoriesDB->toArray());

	       	$catIds = [];
	        foreach ($categoriesDB as $category)
	        {
	            $catIds[$category->id] = $category->id;
	        }
	        // dd($catIds);

	        $subcategoriesDB = Category::where("status", "published")
	            ->whereIn("parent_id", $catIds)
	            // ->orderBy("name")
	            ->get(["id", "name", "parent_id"]);
	        // dd($subcategoriesDB->toArray());
	        
	        $subcatIds = [];
	        $subcatParentIds = [];
	        $subcatNames = [];

	        foreach ($subcategoriesDB as $subcategory)
	        {
	            $subcatIds[$subcategory->id] = $subcategory->id;
	            $subcatParentIds[$subcategory->id] = $subcategory->parent_id;
	            $subcatNames[$subcategory->id] = $subcategory->name;
	        }
	        // dd($subcatParentIds);
	        
	        $branches = DB::table('branches as b')
	            ->join("branch_category as p", "b.id", "=", "p.branch_id")
	            // ->whereIn("p.category_id", $subcatIds)
	            // ->join('categories as c', 'c.id', '=', 'p.category_id')
	            // ->where('c.status', 'published')
	            ->where('b.status', 'published')
	            ->where("b.city_id", $city->id)
	            ->orderBy("b.created_at", "DESC")
	            ->select(["b.id", "b.name", "b.is_featured", "b.created_at", 'p.category_id'])
	            ->get();
	        // dd(count($branches));

	        $categories = [];
	        $uniqueSubcatIds = [];
	        $featured = [];
	        $latest = [];
	        $maxLatests = 16;
	        
	        foreach ($branches as $key => $branch)
	        {
	            if (!in_array($branch->category_id, $subcatIds)) continue;

	            if ($branch->is_featured == 1) 
	            {
	                $featured[] = $branch;
	            }
	            elseif ($maxLatests > 0)
	            {
	                $latest[] = $branch;
	                $maxLatests -= 1;
	            } 

	            if (!isset($uniqueSubcatIds[$branch->category_id]))
	            {
	                $uniqueSubcatIds[$branch->category_id] = $branch->category_id;
	            }
	        }
	        // dd($latest);
	        // dd($uniqueSubcatIds);
	        
	        $categories = [];
	        foreach ($categoriesDB as $category)
	        {
	            foreach ($subcatParentIds as $subcatId => $parentId)
	            {
	                if (in_array($subcatId, $uniqueSubcatIds) && $parentId == $category->id)
	                {
	                    $categories[$category->id] = $category;
	                }
	            }
	        }
	        // dd($categories);

	        foreach ($categories as $category)
	        {
	        	DB::table('view_categories')->insert([
	        		'category_id' => $category->id,
	        		'parent_id' => 0,
	        		'city_id' => $city->id,
	        		'category_name' => $category->name,
	        		'category_slug' => $category->slug,
	        		'category_icon' => $category->icon,
	        		'orgs_count' => 0
	    		]);
	        }
    	}

    	

        dd("DONE");
    }


    public function subcategories()
    {
    	DB::table('view_subcategories')->delete();
    	$cities = City::published()->correct()->orderBy('order')->get();

    	foreach ($cities as $city)
    	{
    		// dd($city);
    		$subcategoriesDB = Category::where("status", "published")
    			->where('parent_id', '!=', null)
    			->get(["id", "name", "parent_id", "slug"]);
		    // dd($subcategoriesDB->toArray());
	        
	        foreach ($subcategoriesDB as $subcategory)
	        {
	        	$branches = DB::table('branches')
	        		->join('branch_category as p', 'p.branch_id', '=', 'branches.id')
	        		->where('city_id', $city->id)
	        		->where('p.category_id', $subcategory->id)
	        		->where('status', 'published')
	        		->get(['organization_id', 'name', 'branch_id']);

	        	$orgs = [];
	        	
	        	foreach ($branches as $branch)
	        	{
	        		$orgs[$branch->organization_id] = $branch->organization_id;
	        	}

	        	DB::table('view_subcategories')->insert([
	        		'category_id' => $subcategory->id,
	        		'parent_id' => $subcategory->parent_id,
	        		'city_id' => $city->id,
	        		'category_name' => $subcategory->name,
	        		'category_slug' => $subcategory->slug,
	        		'orgs_count' => count($orgs)
        		]);
	        }
        }

        dd("DONE");
    }

    public function organizations()
    {
    	DB::table('view_organizations')->delete();

    	$cities = [];
    	foreach (City::published()->correct()->get() as $city)
    	{
    		$cities[$city->id] = $city;
    	}

    	$categories = [];
    	foreach (Category::published()->get() as $category)
    	{
    		$categories[$category->id] = $category;
    	}

    	$toptens = [];
    	$toptensDB = DB::table('toptens')->get();
    	foreach ($toptensDB as $topten)
    	{
    		if (isset($toptens[$topten->organization_id]))
    		{
    			$toptens[$topten->organization_id][] = $topten;
    			continue;
    		}

    		$toptens[$topten->organization_id] = [];
    		$toptens[$topten->organization_id][] = $topten;
    	}
    	// dd($toptens);

    	// $orgs = [];
    	$orgsDB = Organization::published()->get();

    	foreach ($orgsDB as $org)
    	{
    		$orgBranches = [];
    		foreach (Branch::published()->where('organization_id', $org->id)->get() as $orgBranch)
    		{
    			$orgBranches[$orgBranch->id] = $orgBranch;
    		}
    		
    		$orgCats = [];    		
    		foreach (DB::table('branch_category')->whereIn('branch_id', $orgBranches)->get() as $orgCat)
    		{
    			$orgCats[$orgCat->category_id] = $orgCat;
    		}
    		
    		// photo
    		// 

    		if (!empty($orgCats))
    		{
    			foreach ($orgCats as $orgCat)
    			{
    				DB::table('view_organizations')->insert([
    					'org_id' => $org->id,
    					'org_name' => $org->name,
    					'org_description' => $org->description,
    					'city_id' => $orgBranch->city_id,
    					'cat_id' => $orgCat->category_id,
    					'cat_slug' => $categories[$orgCat->category_id]->slug,
    					'cat_name' => $categories[$orgCat->category_id]->name,
    					'order' => isset($toptens[$org->id][$org->category_id]) ? $toptens[$org->id][$org->category_id] : 999999
					]);
					dd("DONE");
    			}
    		}
    	}

    	// $branches = [];
    	// foreach (Branch::published()->get() as $branch)
    	// {
    	// 	$branches[$branch->id] = $branch;
    	// }
    }
}
