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
        $action = $this->argument('name');

        switch ($action) 
        {
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

        $this->info("Done");
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
        $orgsDB = Organization::published()->get();
        $bar = $this->output->createProgressBar(count($orgsDB));

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
                }
            }
            else
            {
                $this->line("Skipping " . $org->id . " - no category for branches");
            }

            $bar->advance();
        }

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

        foreach ($orgs as $org)
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
