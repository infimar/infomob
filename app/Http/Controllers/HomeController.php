<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use App\Category;
use App\Organization;
use App\City;
use App\Branch;
use View;
use DB;

class HomeController extends InfomobController
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

        if ($this->cityId == 0)
        {
            $this->city = City::correct()->orderBy('order')->first();
            $this->cityId = $this->city->id;

            View::share('chosenCity', $this->city);
            JavaScript::put(["chosenCity" => $this->city, "chosenCategory" => $this->category]);
        } 
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categoriesDB = Category::roots()->published()
            ->where("parent_id", null)
            ->orderBy("name", "ASC")
            ->get();

        $categories = [];
        foreach ($categoriesDB as $key => $category) 
        {
            if ($category->descendants()->limitDepth(1)->count() > 0)
            {
                $categories[] = $category;
            }
        }

        $featured = Branch::with(['categories', 'photos'])->where("is_featured", 1)->get();
        $latest = Branch::with(['categories'])->orderBy('created_at', 'DESC')->limit(16)->get();

        return view('layouts.frontend.index', compact('categories', "featured", "latest"));
    }

    public function category(Request $request, $slug)
    {
        $activeSubcategory = null;
        $category = Category::published()->where("slug", $slug)->first(); 
        // dd($category);
        
        $children = $category->descendants()->limitDepth(1)->published()->get();

        // TODO: what if there are no subcategories?

        // which subcategory to show?
        if ($request->has("subcategory"))
        {
            foreach ($children as $child)
            {
                if ($child->slug == $request->input("subcategory")) $activeSubcategory = $child;
            }
        } else {
            $activeSubcategory = $children[0];
        }

        // get organizations for activeSubcategory
        $cityId = $this->city->id;
        $categoryId = $activeSubcategory->id;
        
        // toptens
        $toptens = DB::table('toptens')
            ->where('city_id', $cityId)
            ->where('category_id', $categoryId)
            ->get();

        $toptenIds = DB::table('toptens')
            ->where('city_id', $cityId)
            ->where('category_id', $categoryId)
            ->lists('id');

        // rest organizations
        $organizations = Organization::published()
            // ->orderBy("name", "ASC")
            ->orderBy("order", "ASC")
            ->whereHas("branches", function ($query) use ($cityId, $categoryId) {
                $query->published();
                $query->whereHas("city", function ($query) use ($cityId) {
                    $query->where("id", $cityId);
                });
                $query->whereHas("categories", function ($query) use ($categoryId) {
                    $query->published();
                    $query->where("category_id", $categoryId); 
                });
            })
            ->with(["branches" => function ($query) {
                $query
                    ->with(["phones", "photos"])
                    ->where("type", "main")->get();
            }])
            ->whereNotIn('id', $toptenIds)
            ->paginate(10);

        // TODO: what if there is no main branch???
        // dd($organizations);

        return view('layouts.frontend.category', compact('category', 'children', 'activeSubcategory', "organizations"));
    }

    public function organization(Request $request, $organizationId, $categoryId)
    {
        try
        {
            $organization = Organization::published()->findOrFail($organizationId);
            $cityId = $this->city->id;
            
            // category
            $subcategory = Category::findOrFail($categoryId);
            $category = $subcategory->parent()->first();

            // for the city
            $branches = Branch::published()
                ->orderBy("name", "ASC")
                ->where("organization_id", $organization->id)
                ->whereHas("city", function ($query) use ($cityId) {
                    $query->where("id", $cityId);
                })
                ->whereHas("categories", function($query) use ($categoryId) {
                    $query->published();
                    $query->where("category_id", $categoryId);
                })
                ->with(["city"])
                ->get(["id", "name", "address", "type", "city_id"]);
            
            // if one branch
            // redirect
            if (count($branches) == 1)
            {
                return redirect()->action('HomeController@branch', ['id' => $branches[0]->id, 'category_id' => $subcategory->id]);
            }

            // other branches
            $otherBranches = Branch::published()
                ->orderBy("name", "ASC")
                ->where("organization_id", $organization->id)
                ->whereHas("city", function ($query) use ($cityId) {
                    $query->where("id", "!=", $cityId);
                })
                ->with(["city"])
                ->get(["id", "name", "address", "type", "city_id"]);
            
            // if there is no -> notfound
            // TODO: no branches for organization
            
            // dd($branches);
            // dd($otherBranches);

            return view("layouts.frontend.organization", compact("organization", "branches", "otherBranches", "category", "subcategory"))
                ->with("city", $this->city);
        }
        catch (\Exception $e)
        {
            dd($e->getMessage());
            // abort(404);
        }
    }

    public function branch($id, $categoryId)
    {        
        $cityId = $this->city->id;

        try
        {
            $branch = Branch::published()->with([
                "phones" => function ($query) {
                    $query
                        ->select(["branch_id", "type", "code_country", "code_operator", "number", "contact_person"]);
                }, 
                "socials" => function ($query) {
                    $query
                        ->select(["branch_id", "type", "name", "contact_person"]);
                },
                "photos" => function ($query) {
                    $query
                        ->where("type", "picture")
                        ->select(["branch_id", "type", "path", "description"]);
                }, 
                "organization" => function ($query) {
                    $query->select(["id", "name"]);
                },
                "categories" => function ($query) {
                    $query->published();
                    $query->select(["id", "name", "parent_id"]);
                }])
                ->findOrFail($id);
                
            // category
            $category = Category::findOrFail($categoryId);
            $parentCategory = $category->parent()->first();

            $categoryLabel = $parentCategory->name . " / " . $category->name;
            

            // other branches?
            $otherBranches = Branch::published()->where("organization_id", $branch->organization->id)
                ->with([
                    "city" => function ($query) {
                        $query->select(["id", "name"]);
                }])
                ->where("id", "!=", $branch->id)
                ->get(["id", "name", "address", "city_id"]);
            
            // check
            // dd($branches);
            // dd($otherBranches);
            
            // phone types
            $types = [
                'work' => 'Рабочий',
                'mobile' => 'Мобильный',
                'home' => 'Домашний',
                'fax' => 'Факс',
                'whatsapp' => 'Whatsapp',
                'viber' => 'Viber',
                'telegram' => 'Telegram',
            ];
            
            return view("layouts.frontend.branch", compact('branch', 'otherBranches', 'categoryLabel', 'types', 'category', 'parentCategory'));
        }
        catch (Exception $e)
        {
            dd($e->getMessage());
            // abort(404);   
        }
    }
}
