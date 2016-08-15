<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Category;
use App\Branch;
use App\Organization;
use App\City;

class CreateViewTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'viewtable:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create view table for faster website load';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time_start = microtime(true);
        $action = $this->argument('name');

        switch ($action) 
        {
            case 'categories':
                $this->categories();
                break;

            case 'subcategories':
                $this->subcategoriesOpt();
                break;

            case 'orgs':
                $this->organizations();
                break;

            case 'photos':
                $this->photos();
                break;

            case 'toptens':
                $this->toptens();
                break;

            case 'phones':
                $this->phones();
                break;

            case 'featured':
                $this->featured();
                break;
            
            default:
                $this->info("Wrong action name");
                break;
        }

        $time_end = microtime(true);
        $this->info("Done in " . ($time_end - $time_start) . " seconds");
    }

    private function categories()
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
    }

    private function subcategoriesOpt()
    {
        DB::table('view_subcategories')->delete();
        $cities = City::published()->correct()->orderBy('order')->get();
        $bar = $this->output->createProgressBar(count($cities));

        $subcategories = [];
        $subcategoriesDB = DB::table('categories')->where('parent_id', '!=', null)->get();

        foreach ($subcategoriesDB as $subcat)
        {
            $subcategories[$subcat->id] = $subcat;
        }

        foreach ($cities as $key => $city) 
        {
            $orgs = DB::table('view_organizations')->where('city_id', $city->id)->get();
            // dd(count($orgs));
            
            $cats = [];
            $catIds = [];
            $catNames = [];
            $catSlugs = [];
            $catCounts = [];

            foreach (array_chunk($orgs, 1000) as $chunk)
            {
                foreach ($chunk as $org)
                {
                    $catIds[$org->cat_id] = $org->cat_id;
                    $catNames[$org->cat_id] = $org->cat_name;
                    $catSlugs[$org->cat_id] = $org->cat_slug;

                    if (isset($catCounts[$org->cat_id])) $catCounts[$org->cat_id] += 1;
                    else $catCounts[$org->cat_id] = 1;
                }
            }

            foreach ($catCounts as $catId => $count)
            {
                $cats[] = [
                    'category_id' => $catIds[$catId],
                    'parent_id' => $subcategories[$catId]->parent_id,
                    'city_id' => $city->id,
                    'category_name' => $catNames[$catId],
                    'category_slug' => $catSlugs[$catId],
                    'orgs_count' => $count
                ];
            }

            DB::table('view_subcategories')->insert($cats);
            $bar->advance();
        }

        $bar->finish();
    }

    private function subcategories()
    {
        DB::table('view_subcategories')->delete();
        $cities = City::published()->correct()->orderBy('order')->get();
        $bar = $this->output->createProgressBar(count($cities));

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

            $bar->advance();
            $this->line($city->name);
        }

        $bar->finish();
    }

    private function organizations()
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
        $count = DB::table('organizations')->where('status', 'published')->count();
        $bar = $this->output->createProgressBar($count);

        DB::table('organizations')->where('status', 'published')->chunk(1000, function($orgsDB) use (&$bar, &$categories)
        {
            $orgs = [];

            foreach ($orgsDB as $key => $org)
            {
                $orgBranches = [];
                $orgBranchesIds = [];
                foreach (Branch::published()->where('organization_id', $org->id)->get(['id', 'city_id']) as $orgBranch)
                {
                    $orgBranches[$orgBranch->id] = $orgBranch;
                    $orgBranchesIds[] = $orgBranch->id;
                }
                // dd($orgBranches);
                
                $orgCats = [];          
                foreach (DB::table('branch_category')->whereIn('branch_id', $orgBranchesIds)->get(['branch_id', 'category_id']) as $orgCat)
                {
                    $orgCats[$orgCat->category_id] = $orgCat;
                }

                if (!empty($orgCats))
                {
                    foreach ($orgCats as $orgCat)
                    {
                        $orgs[] = [
                            'org_id' => $org->id,
                            'org_name' => $org->name,
                            'org_description' => $org->description,
                            'city_id' => $orgBranch->city_id,
                            'cat_id' => $orgCat->category_id,
                            'cat_slug' => $categories[$orgCat->category_id]->slug,
                            'cat_name' => $categories[$orgCat->category_id]->name,
                            'order' => 999999
                        ];
                    }
                }
                else
                {
                    $this->line("Skipping " . $org->id . " - no category for branches");
                }

                $bar->advance();
            }

            DB::table('view_organizations')->insert($orgs);
        });
        

        $bar->finish();
    }

    private function photos()
    {
        DB::table('view_organizations')->update(['org_photo' => ""]);

        $orgs = DB::table('view_organizations')->get();
        $bar = $this->output->createProgressBar(count($orgs));

        foreach ($orgs as $org)
        {
            $branches = DB::table('branches')->where('organization_id', $org->org_id)->lists('id');
            $photos = DB::table('photos')->whereIn('branch_id', $branches)->get();

            if (!empty($photos))
            {
                $this->info("Photo for " . $org->org_id . " is set");
                DB::table('view_organizations')->where('id', $org->id)->update(['org_photo' => $photos[0]->path]);
            }

            $bar->advance();
        }

        $bar->finish();
    }

    private function toptens()
    {
        DB::table('view_organizations')->update(['order' => 999999]);

        $toptens = DB::table('toptens')->get();
        $bar = $this->output->createProgressBar(count($toptens));

        foreach ($toptens as $topten)
        {
            DB::table('view_organizations')
                ->where('city_id', $topten->city_id)
                ->where('org_id', $topten->organization_id)
                ->where('cat_id', $topten->category_id)
                ->update(['order' => $topten->order]);

            $bar->advance();
        }

        $bar->finish();
    }

    private function phones()
    {
        DB::table('view_organizations')->update(['org_phones' => '']);

        $orgs = DB::table('view_organizations')->get();
        $bar = $this->output->createProgressBar(count($orgs));

        foreach (array_chunk($orgs, 1000) as $chunk)
        {
            foreach ($chunk as $org)
            {
                $branchesIds = DB::table('branches')->where('organization_id', $org->org_id)->lists('id');
                $phones = DB::table('phones')->whereIn('branch_id', $branchesIds)->get();

                $orgPhones = [];
                foreach ($phones as $phone)
                {
                    if ($phone->code_operator == "fix") continue;

                    if ($phone->code_operator == "short_numb") $orgPhones[] = $phone->number;
                    else $orgPhones[] = $phone->code_country . "(" . $phone->code_operator . ")" . " " . $phone->number;
                }

                DB::table('view_organizations')->where('id', $org->id)->update(['org_phones' => implode(";", $orgPhones)]);

                $bar->advance();
            }
        }

        $bar->finish();
    }

    private function featured()
    {
        DB::table('view_featured')->delete();

        $count = DB::table('branches')->where('is_featured', 1)->where('status', 'published')->count();
        $bar = $this->output->createProgressBar($count);

        DB::table('branches')
            ->where('is_featured', 1)
            ->where('status', 'published')
            ->chunk(1000, function($branches) use (&$bar)
        {
            foreach ($branches as $branch)
            {
                // categories
                $categoriesIds = DB::table('branch_category')->where('branch_id', $branch->id)->limit(2)->lists('category_id');
                $categoriesDB = DB::table('categories')->whereIn('id', $categoriesIds)->get();

                $categories = [];
                foreach ($categoriesDB as $category)
                {
                    $categories[] = $category->name;
                }

                // photo
                $photoPath = "";
                $photo = DB::table('photos')->where('branch_id', $branch->id)->limit(1)->first();
                if (!is_null($photo)) $photoPath = $photo->path;

                DB::table('view_featured')->insert([
                    'city_id' => $branch->city_id,
                    'branch_id' => $branch->id,
                    'name' => $branch->name,
                    'org_id' => $branch->organization_id,
                    'categories' => implode(', ', $categories),
                    'photo' => $photoPath
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
    }
}
