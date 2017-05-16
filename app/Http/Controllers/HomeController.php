<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use App\Category;
use App\Organization;
use App\City;
use App\Branch;
use App\Photo;
use App\Phone;
use View;
use DB;
use JavaScript;

class HomeController extends InfomobController
{
    protected $query;


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


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = DB::table('view_categories')
            ->where('city_id', $this->city->id)
            ->where('parent_id', 0)
            ->orderBy('category_name', "ASC")
            ->get();

        // featured
        $featured = DB::table('view_featured')
            ->where('city_id', $this->city->id)
            ->get();

        // latest
        $latest = DB::table('branches')
            ->where('status', 'published')
            ->where('city_id', $this->city->id)
            ->orderBy('created_at', 'DESC')
            ->limit(12)
            ->get();


        // TODO: from db
        $slider = [
            1 => ['shymkent_01.jpg', 'shymkent_02.jpg', 'shymkent_03.jpg'],
            2 => ['astana_01.jpg', 'astana_02.jpg', 'astana_03.jpg'],
            3 => ['almaty_01.jpg', 'almaty_02.jpg', 'almaty_03.jpg'],
            4 => ['kyzylorda_01.jpg', 'kyzylorda_02.jpg', 'kyzylorda_03.jpg'],
            5 => ['karagandy_01.jpg', 'karagandy_02.jpg', 'karagandy_03.jpg'],
            6 => ['taraz_01.jpg', 'taraz_02.jpg', 'taraz_03.jpg'],
            7 => ['ustkaman_01.jpg', 'ustkaman_02.jpg', 'ustkaman_03.jpg'],
            8 => ['semei_01.jpg', 'semei_02.jpg', 'semei_03.jpg'],
            9 => ['kokshetau_01.jpg', 'kokshetau_02.jpg', 'kokshetau_03.jpg'],
            10 => ['kostanai_01.jpg', 'kostanai_02.jpg', 'kostanai_03.jpg'],
            11 => ['aktobe_01.jpg', 'aktobe_02.jpg', 'aktobe_03.jpg'],
            12 => ['uralsk_01.jpg', 'uralsk_02.jpg', 'uralsk_03.jpg'],
            13 => ['atyrau_01.jpg', 'atyrau_02.jpg', 'atyrau_03.jpg'],
            14 => ['aktau_01.jpg', 'aktau_02.jpg', 'aktau_03.jpg'],
        ];

        return view('layouts.frontend.index', compact('categories', 'featured', 'latest', 'slider'));
    }

    public function category(Request $request, $slug)
    {
        $activeSubcategory = null;
        $city = $this->city;
        $perPage = 10;
        $page = $request->input("page", 1);

        try
        {
            $category = Category::published()->where("slug", $slug)->first();
            
            if ($category == null) abort(404);

            // if child?
            if ($category->parent_id != null)
            {
                // get parent
                $parent = Category::where('id', $category->parent_id)->first();
                return redirect()->action('HomeController@category', ['slug' => $parent->slug, 'subcategory' => $category->slug]);
            }

            $subcategories = DB::table('view_subcategories')
                ->where('city_id', $this->city->id)
                ->where('parent_id', $category->id)
                ->where('orgs_count', '>', 0)
                ->orderBy('order', 'ASC')
                ->orderBy('category_name', 'ASC')
                ->get();
            // dd($subcategories);

            // what if subcategories are empty?
            if (count($subcategories) > 0)
            {
                $activeSubcategorySlug = $request->get('subcategory', $subcategories[0]->category_slug);

                // active subcategory
                foreach ($subcategories as $subcategory)
                {
                    if ($subcategory->category_slug == $activeSubcategorySlug) $activeSubcategory = $subcategory;
                }
            }
            
            // there is no subcategories
            // dd($activeSubcategory);
            if (is_null($activeSubcategory))
            {
                return redirect()->action('HomeController@index');
            }            

            // TODO: orgs with order (topten), photo, phones
            $organizations = DB::table('view_organizations')
                ->where('city_id', $this->city->id)
                ->where('cat_id', $activeSubcategory->category_id)
                ->where('status', 'published')
                ->orderBy('order', 'ASC')
                ->orderBy('org_name', 'ASC')
                ->paginate($perPage);
            // dd($organizations);

            return view('layouts.frontend.category', compact('category', 'activeSubcategory', 'subcategories', 'organizations'));
        }
        catch (Exception $e)
        {
            abort(404);
        }
    }

    public function organization(Request $request, $organizationId, $categoryId = 0)
    {
        $cityId = $this->city->id;
        
        try
        {
            $organization = Organization::published()->findOrFail($organizationId);
            // dd($organization);
            
            // category
            $subcategory = $category = null;
            if ($categoryId != 0)
            {
                $subcategory = Category::findOrFail($categoryId);
                $category = $subcategory->parent()->first();
            }
            else
            {
                $branchIds = Branch::published()->where('organization_id', $organization->id)->lists('id');
                $categories = DB::table('branch_category')->where('branch_id', $branchIds)->lists('category_id');
                
                if ($categories)
                {
                    $categoryId = $categories[0];
                    $subcategory = Category::findOrFail($categoryId);
                    $category = $subcategory->parent()->first();
                }
                else
                {

                }
            }

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
            
            // dd($branches);

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

    public function branch($id, $categoryId = 0)
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

            // dd($branch);
            
            // check city
            if ($branch->city_id != $cityId)
            {
                return redirect()->action('HomeController@index');
            }

            // category
            $category = $parentCategory = $categories = null;
            $categoryLabel = ""; 
            
            if ($categoryId)
            {
                $category = Category::findOrFail($categoryId);
                $parentCategory = $category->parent()->first();
                $categoryLabel = $parentCategory->name . " / " . $category->name;
            }
            else
            {
                $categoriesIds = DB::table('branch_category')->where('branch_id', $branch->id)->lists('category_id');
                $categories = Category::whereIn('id', $categoriesIds)->get();
                
                if ($categories->count() != 0) 
                { 
                    $category = $categories[0];
                    $parentCategory = $category->parent()->first();
                    $categoryLabel = $parentCategory->name . " / " . $category->name;
                }
            }
            
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

            // inc hits
            $branch->hits += 1;
            $branch->save();
            // dd($branch);

            JavaScript::put(['pointLat' => $branch->lat, 'pointLng' => $branch->lng]);
            
            return view("layouts.frontend.branch", compact('branch', 'otherBranches', 'categoryLabel', 'types', 'category', 'parentCategory', 'categories'));
        }
        catch (Exception $e)
        {
            dd($e->getMessage());
            // abort(404);   
        }
    }

    /**
     * Search
     * @return response Http response
     */
    public function search(Request $request)
    {
        $branches = [];
        $query = $this->query;
        $noQuery = ($query == "") ? true : false;
        $perPage = 25;

        if (!$noQuery)
        {
            // categories
            $categories = DB::table('categories')
                ->where('status', 'published')
                ->where('name', 'LIKE', '%' . $query . '%')
                ->orderBy('name', 'ASC')
                ->get(['id', 'name', 'slug']);

            // by tags
            $taggables = DB::table('tagging_tagged')
                ->where('taggable_type', 'App\Branch')
                ->where('tag_name', 'LIKE', '%' . $query . '%')
                ->lists('taggable_id');

            $branchesByTags = Branch::whereIn('id', $taggables)
                ->published()
                ->where('city_id', $this->city->id)
                ->get(['id', 'name', 'organization_id']);

            // by categories
            $categoryIds = [];
            foreach ($categories as $category)
            {
                $categoryIds[] = $category->id;
            }

            $branchIds = DB::table('branch_category')
                ->whereIn('category_id', $categoryIds)
                ->lists('branch_id');

            $branchesByCategories = Branch::published()
                ->whereIn('id', $branchIds)
                ->where('city_id', $this->city->id)
                ->orderBy('name', 'ASC')
                ->get(['id', 'name', 'organization_id']);

            $toptens = [];
            if (count($branchesByCategories) > 0)
            {
                $toptens = DB::table('toptens')
                    ->where('city_id', $this->city->id)
                    ->whereIn('category_id', $categoryIds)
                    ->lists('organization_id');
            }

            // by name
            $branchesByName = Branch::published()
                ->where('name', 'LIKE', '%' . $query . '%')
                ->where('city_id', $this->city->id)
                ->orderBy('name', 'ASC')
                ->get(['id', 'name', 'organization_id']);

            // by description
            $branchesByDescription = Branch::published()
                ->where('description', 'LIKE', '%' . $query . '%')
                ->where('city_id', $this->city->id)
                ->orderBy('name', 'ASC')
                ->get(['id', 'name', 'organization_id']);

            // merge results
            $branches = [];

            foreach ($branchesByTags as $branch)
            {
                $branches[$branch->id] = $branch->toArray();
            }

            foreach ($branchesByCategories as $branch)
            {
                $branches[$branch->id] = $branch->toArray();
            }

            foreach ($branchesByName as $branch)
            {
                $branches[$branch->id] = $branch->toArray();
            }

            foreach ($branchesByDescription as $branch)
            {
                $branches[$branch->id] = $branch->toArray();
            }

            // obey order
            $sorted = [];
            foreach ($toptens as $topten)
            {
                foreach ($branches as $key => $branch)
                {
                    if ($branch['organization_id'] == $topten)
                    {
                        $sorted[] = $branch;
                        unset($branches[$key]);
                        break;
                    }
                }
            }

            $branches = $sorted + $branches;
            $result = [];

            $pageNum = $request->get('page', 1);
            $start = ($pageNum - 1) * $perPage;
            $end = ($pageNum - 1) * $perPage + $perPage;
            $idx = 0;
            $length = count($branches);

            foreach ($branches as $key => $branch)
            {
                // if ($idx > $length - 1) break;
                $idx += 1;

                if ($idx < $start + 1) continue;
                else if ($idx > $end) break;

                if (!DB::table('branch_category')->where('branch_id', $branch['id'])->first()) continue;

                $result[] = $branch;
            }
            
            $links = [];
            $pagesCount = intval(ceil($length / $perPage)); 

            // dd([$pageNum, $start, $end, $result, $length, $pagesCount]);

            // dd([
            //     'search' => $this->query,
            //     'categories' => $categories,
            //     'branches' => $branches,
            //     'result' => 
            //     [
            //         'by tags' => $branchesByTags->toArray(),
            //         'by categories' => $branchesByCategories->toArray(),
            //         'by name' => $branchesByName->toArray(),
            //         'by description' => $branchesByDescription->toArray()
            //     ]
            // ]);
        }        

        return view('layouts.frontend.search', compact('query', 'branches', 'result', 'pagesCount', 'pageNum', 'categories', 'noQuery'));
    }


    /**
     * Contacts
     */
    public function contacts()
    {
        // TODO: from db
        switch ($this->city->id) {
            default:
                $address = "город Шымкент, улица Алдиярова, дом 12а, кв №15";
                $contacts = [
                    ['work', '8 (7252) 540 992'], 
                    ['mobile', '+7 (778) 577 2225'], 
                    ['mobile', '+7 (778) 577 2226'],
                ];
                $socials = [
                    ['facebook', 'https://www.facebook.com/infomob.kazakhstan'],
                    ['vk', 'https://vk.com/infomobkz'],
                    ['instagram', 'https://www.instagram.com/infomobkz'],
                ];

                break;
        }

        return view('layouts.frontend.contacts', compact('address', 'contacts', 'socials'));
    }
}
