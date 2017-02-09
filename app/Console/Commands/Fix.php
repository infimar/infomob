<?php

namespace App\Console\Commands;

use App\Branch;
use App\Category;
use App\City;
use App\Organization;
use App\Phone;
use DB;
use File;
use Illuminate\Console\Command;

class Fix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:fix {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix some tables in DB';

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
            case 'gisphones':
                $this->gisphones();
                break;

            case 'orgs':
                $this->orgs(50000);
                break;

            case 'orgsMark':
                $this->orgsMarkDelete();
                break;

            case 'branches':
                $this->branches();
                break;

            case 'branch-category':
                $this->branchCategory();
                break;

            case 'branchesMark':
                $this->branchesMarkAsDelete();
                break;

            case 'phones':
                $this->phones();
                break;

            case 'phonesMark':
                $this->phonesMarkAsDelete();
                break;

            case 'socials':
                $this->socials();
                break;

            case 'socialsMark':
                $this->socialsMarksAsDelete();
                break;

            case 'removeDuplicates':
                $this->removeDuplicates();
                break;

            case 'phone-numbers':
                $this->phoneNumbers();
                break;
            
            case 'phone-codes':
                $this->phoneCodes();
                break;

            case 'orgpages':
                $this->orgpages();
                break;

            case 'clear':
                $this->clearBranches();
                break;

            case 'removeCity':
                $this->removeCity();
                break;

            case 'atameken':
                $this->atameken();
                break;

            case 'emptyphones':
                $this->emptyphones();
                break;

            case 'openhours':
                $this->openhours('atm');
                break;

            default:
                $this->info("Wrong action name");
                break;
        }

        $time_end = microtime(true);
        $this->info("Done in " . ($time_end - $time_start) . " seconds");
    }


    private function openhours($model)
    {
        // $ids = [];
        // for ($i = 182183; $i <= 182208; $i++) { $ids[] = $i; }
        $ids = [181942, 181943, 181944, 181945, 181946, 181947, 181948, 181949, 181950, 182005, 182006, 182007, 182008, 182009, 182010, 182012, 182218, 182219, 182220, 182221, 182222, 182223, 182224, 182225, 182226, 182227, 182228, 182229, 182230, 182231, 182232, 182233, 182234, 182235];

        foreach ($ids as $id)
        {
            $data = [
                'monday_atc' => 1,
                'tuesday_atc' => 1,
                'wednesday_atc' => 1,
                'thursday_atc' => 1,
                'friday_atc' => 1,
                'saturday_atc' => 1,
                'sunday_atc' => 1,
                'branch_id' => $id
            ];

            // insert 
            DB::table('open_hours')->insert($data);
        }
    }

    private function emptyphones()
    {
        $file = public_path() . '/data/emptyphones.txt';
        
        Phone::with('branch')->where('number', '')->chunk(200, function($phones) use ($file)
        {
            foreach ($phones as $phone)
            {
                $output = $phone->branch->id . ' - ' . $phone->branch->name . "\n";
                File::append($file, $output);
            }
        });
    }


    private function atameken()
    {
        $atamekenCategoryId = 140;
        $categories = Category::where('parent_id', $atamekenCategoryId)->get();

        $total = Branch::whereHas('categories', function ($query) use ($categories) {
            $query->whereIn('category_id', $categories->pluck('id'));
        })->where('city_id', 1)->count();
        $bar = $this->output->createProgressBar($total);

        Branch::whereHas('categories', function ($query) use ($categories) {
            $query->whereIn('category_id', $categories->pluck('id'));
        })->with('socials', 'categories')->where('city_id', 1)->chunk(200, function ($branches) use ($bar, $atamekenCategoryId)
        {
            foreach ($branches as $branch)
            {
                foreach ($branch->categories as $category)
                {
                    if ($category->parent_id != $atamekenCategoryId)
                    {
                        // dd($branch->toArray());
                        DB::table('branch_category')
                            ->where('branch_id', $branch->id)
                            ->where('category_id', $category->id)
                            ->delete();
                    }
                }

                $socials = collect();
                foreach ($branch->socials as $social)
                {
                    if (empty($social->name))
                    {
                        DB::table('socials')->where('id', $social->id)->delete();
                        continue;
                    }

                    if ($social->type == "website" && !empty($social->name)) $socials->push($social);
                }

                if ($socials->count() > 1)
                {
                    $website = $socials->first();
                    
                    foreach ($socials as $social)
                    {
                        if ($website->name == $social->name && $website->id != $social->id)
                        {
                            DB::table('socials')->where('id', $social->id)->delete();
                        }
                    }
                }

                $bar->advance();
            }
        });

        $bar->finish();
    }


    private function removeCity()
    {
        $cityId = 4;

        try
        {
            $city = City::findOrFail($cityId);
            
            $total = Branch::where("city_id", $city->id)->count();
            $bar = $this->output->createProgressBar($total);

            Branch::with(['phones', 'photos', 'socials'])
                ->where("city_id", $city->id)->chunk(200, function($branches) use ($bar)
            {
                foreach ($branches as $branch)
                {
                    // dd($branch);
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

                    $bar->advance();
                }
            });

            $bar->finish();
        }
        catch (Exception $e)
        {
            $this->info($e->getMessage());
        }
    }


    private function gisphones()
    {
        // $cityId = 3;
        $path = public_path() . "/data/";
        $files = File::allFiles($path);
        // $output = public_path() . '/gisphones.txt';

        $inc = 0;
        $total = count($files);
        $totalOrgs = 0;
        $bar = $this->output->createProgressBar($total);

        $shortNumber = false;
        $type = $text = $code = $number = $fp = $lp = $fd = $sd = '';

        foreach ($files as $key => $file)
        {
            $inc += 1;
            $data = json_decode(File::get($file));
            // dd($data);

            foreach ($data->result->items as $item)
            {
                $phones = [];

                // get phones from data
                foreach ($item->contact_groups as $contactGroup)
                {
                    foreach ($contactGroup->contacts as $contact)
                    {
                        if (!in_array($contact->type, ['phone', 'fax'])) continue;

                        // if it is full number
                        if (mb_strpos($contact->text, '+7') !== false) 
                        {
                            // work
                            if (mb_strpos($contact->text, '(') !== false)
                            {
                                $type = 'work';

                                $text = str_replace('‒', '', $contact->text);

                                // get last )
                                $fp = mb_strpos($text, '(');
                                $lp = mb_strpos($text, ')');

                                $code = substr($text, $fp + 1, $lp - $fp - 1);
                                $number = substr($text, $lp + 1);
                            }
                            // mobile
                            else
                            {
                                $type = 'mobile';

                                $fd = mb_strpos($contact->text, '‒');
                                $sd = mb_strpos($contact->text, '‒', $fd + 1);

                                $code = mb_substr($contact->text, $fd + 1, $sd - $fd - 1);

                                $text = mb_substr($contact->text, $sd + 1);
                                $number = str_replace('‒', '', $text);
                            }
                        }
                        // short number
                        else
                        {
                            $shortNumber = true;
                            $code = 'short_numb';
                            $number = $contact->value;
                        }

                        // type
                        if ($contact->type == 'fax') $type = 'fax';

                        // append
                        $phones[] = [
                            'origin' => $contact->text,
                            'short_number' => $shortNumber,
                            'type' => $type,
                            'value' => $contact->value,
                            'text' => $text,
                            'code' => trim($code),
                            'number' => trim($number),
                        ];

                        // File::append($output, $contact->text . " - " . $type . ' (' . $code . ') ' . $number . "\n");
                    }
                }
                // dd($phones);

                // look for organizations in db
                $foundOrg = Organization::select(['id', 'name'])
                    ->whereName($item->name)
                    ->whereHas('branches', function($query) {
                        $query->whereCityId(3);
                    })
                    ->first();
                if (!$foundOrg)
                { 
                    // $this->info("Org not found"); 
                    continue; 
                }

                $branch = Branch::whereOrganizationId($foundOrg->id)->select('id', 'organization_id')->first();
                if (!$branch) 
                { 
                    // $this->info("Branch not found"); 
                    continue; 
                }

                $phonesArr = [];
                $phonesDB = Phone::whereBranchId($branch->id)->get();
                // dd($phonesDB->toArray());
                foreach ($phonesDB as $phone)
                {
                    $phonesArr[] = [
                        'id' => $phone->id,
                        'type' => $phone->code_operator,
                        'value' => $phone->code_country . $phone->code_operator . $phone->number,
                        'text' => $phone->code_country . ' (' . $phone->code_operator . ') ' . $phone->number
                    ];
                }

                // dd([
                //     'gis' => $phones,
                //     'db' => $phonesArr
                // ]);

                // compare phones
                $length = count($phones);
                $lengthDB = count($phonesArr);

                for ($i = 0; $i < $length; $i++)
                {
                    // if (strpos($phonesArr[$i]['value'], $phones[$i]['number']))
                    // {
                    //     // $this->info('same: ' . $phones[$i]['value'] . ' - ' . $phonesArr[$j]['value']);
                    // }
                    
                    if (!isset($phonesArr[$i])) continue;
                    
                    $updatedPhone = Phone::find($phonesArr[$i]['id']);
                    $updatedPhone->type = $phones[$i]['type'];
                    $updatedPhone->code_operator = $phones[$i]['code'];
                    $updatedPhone->number = $phones[$i]['number'];
                    $updatedPhone->save();

                    $content = 'id: ' . $updatedPhone->id . ' - ' . $updatedPhone->type . ' - ' . $updatedPhone->code_country . ' (' . $updatedPhone->code_operator . ') ' . $updatedPhone->number . "\n";

                    // $this->info('gis: ' . $phones[$i]['origin'] . ' | ' . $content);
                    // File::append($output, $content);
                }

                // dd("Done");
            }
            
            $totalOrgs += $data->result->total;

            $this->info($total - $inc . " left. Organizations count: " . $totalOrgs);
            $bar->advance();
        }

        $bar->finish();
    }










    private function clearBranches()
    {
        $orgIds = DB::table('organizations')->whereNotNull('notes')->lists('id');

        DB::table('branches')
            ->whereIn('organization_id', $orgIds)
            ->update(['description' => '']);
    }

    private function orgpages()
    {
        $branchesDB = DB::table('branches')->where('description', 'like', '%' . '/n' . '%')->get();
        $bar = $this->output->createProgressBar(count($branchesDB));
        // dd(count($branchesDB));

        foreach ($branchesDB as $branch)
        {
            $description = str_replace("/n", "\n", trim($branch->description));
            DB::table('branches')->where('id', $branch->id)->update(['description' => $description]);
            $bar->advance();
        }

        $bar->finish();
    }


    private function removeDuplicates()
    {
        $bar = $this->output->createProgressBar(4);

        DB::table('organizations')->where('notes', 'delete')->delete();
        $this->line("organizations done");
        $bar->advance();

        DB::table('branches')->where('name', 'delete')->delete();
        $this->line("branches done");
        $bar->advance();

        DB::table('phones')->where('contact_person', 'delete')->delete();
        $this->line("phones done");
        $bar->advance();

        DB::table('socials')->where('contact_person', 'delete')->delete();
        $this->line("socials done");
        $bar->advance();

        $bar->finish();
    }

    private function phonesMarkAsDelete()
    {
        $path = public_path() . "/data/phones/";
        $files = File::allFiles($path);

        $inc = 0;
        $total = count($files);
        $bar = $this->output->createProgressBar($total);

        foreach ($files as $key => $file)
        {
            $inc += 1;
            $phones = json_decode(File::get($file));

            DB::table('phones')->whereIn('id', $phones)->update(['contact_person' => 'delete']);

            $this->info($total - $inc . " left");
            $bar->advance();
        }

        $bar->finish();
    }

    private function phones()
    {
        $inc = 0;
        $count = DB::table('branches')->where('name', 'delete')->count();
        $bar = $this->output->createProgressBar($count);

        DB::table('branches')
            ->where('name', 'delete')
            ->chunk(1000, function($branches) use (&$inc, &$bar) 
        {
            $dataPhones = [];

            // dd($organizations);
            foreach ($branches as $branch)
            {
                $inc += 1;
                  
                $phones = DB::table('phones')
                    ->where('branch_id', $branch->id)
                    ->lists('id');
                // dd($phones);

                if (empty($phones)) continue;

                foreach ($phones as $phone)
                {
                    $dataPhones[] = $phone;
                }
                // dd($dataPhones);
                  
                $bar->advance();
            }

            File::put(public_path() . "/data/phones/" . uniqid() . ".txt", json_encode($dataPhones));
            $this->info($inc);
        });

        $bar->finish();
    }

    private function socialsMarksAsDelete()
    {
        $path = public_path() . "/data/socials/";
        $files = File::allFiles($path);

        $inc = 0;
        $total = count($files);
        $bar = $this->output->createProgressBar($total);

        foreach ($files as $key => $file)
        {
            $inc += 1;
            $socials = json_decode(File::get($file));

            DB::table('socials')->whereIn('id', $socials)->update(['contact_person' => 'delete']);

            $this->info($total - $inc . " left");
            $bar->advance();
        }

        $bar->finish();
    }

    private function socials()
    {
        $inc = 0;
        $count = DB::table('branches')->where('name', 'delete')->count();
        $bar = $this->output->createProgressBar($count);

        DB::table('branches')
            ->where('name', 'delete')
            ->chunk(1000, function($branches) use (&$inc, &$bar) 
        {
            $dataSocials = [];

            // dd($organizations);
            foreach ($branches as $branch)
            {
                $inc += 1;
                  
                $socials = DB::table('socials')
                    ->where('branch_id', $branch->id)
                    ->lists('id');
                // dd($socials);

                if (empty($socials)) continue;

                foreach ($socials as $social)
                {
                    $dataSocials[] = $social;
                }
                // dd($dataPhones);
                
                $bar->advance();
            }

            File::put(public_path() . "/data/socials/" . uniqid() . ".txt", json_encode($dataSocials));
            $this->info($inc);
        });

        $bar->finish();
    }

    private function branchesMarkAsDelete()
    {
        $path = public_path() . "/data/branches/";
        $files = File::allFiles($path);

        $inc = 0;
        $total = count($files);
        $bar = $this->output->createProgressBar($total);

        foreach ($files as $key => $file)
        {
            $inc += 1;
            $branches = json_decode(File::get($file));

            DB::table('branches')->whereIn('id', $branches)->update(['name' => 'delete']);

            $this->info($total - $inc . " left");
            $bar->advance();
        }

        $bar->finish();
    }

    private function branchCategory()
    {
        $path = public_path() . "/data/branches/";
        $files = File::allFiles($path);

        $inc = 0;
        $total = count($files);
        $bar = $this->output->createProgressBar($total);

        foreach ($files as $key => $file)
        {
            $inc += 1;
            $branches = json_decode(File::get($file));
            // dd(count($branches));

            $entries = DB::table('branch_category')->whereIn('branch_id', $branches)->delete();
            // dd(count($entries));

            $this->info($total - $inc . " left");
            $bar->advance();
        }

        $bar->finish();
    }

    private function branches()
    {
        $dataBranches = [];
        $inc = 0;
        $count = DB::table('organizations')->where('notes', 'delete')->count();
        $bar = $this->output->createProgressBar($count);

        DB::table('organizations')
            ->where('notes', 'delete')
            ->chunk(1000, function($organizations) use (&$dataBranches, &$inc, &$bar) 
        {
            // dd($organizations);
            foreach ($organizations as $organization)
            {
                $inc += 1;
                $bar->advance();
                  
                $branches = DB::table('branches')
                    ->where('organization_id', $organization->id)
                    ->lists('id');

                if (empty($branches)) continue;

                foreach ($branches as $branch)
                {
                    $dataBranches[] = $branch;
                }
            }

            File::put(public_path() . "/data/branches/" . uniqid() . ".txt", json_encode($dataBranches));
            $this->info($inc);
        });

        $bar->finish();
    }

    private function orgs($chunkSize = 10000)
    {
        $inc = 0;
        $dataOrgs = [];

        $count = DB::table('organizations')
                // ->where('city_id', 3)
                ->where('id', '>=', 100000)
                ->where('notes', '!=', 'delete')
                ->count();

        $bar = $this->output->createProgressBar($count);

        $orgs = DB::table('organizations')
            // ->where('city_id', 3)
            ->where('id', '>=', 100000)
            ->where('notes', '!=', 'delete')
            ->orderBy('id')
            // ->orderBy('name')
            ->chunk($chunkSize, function($orgs) use (&$inc, &$bar)
            {
                $dataOrgs = [];
                
                foreach ($orgs as $key => $org)
                {
                    $inc += 1;

                    if (!isset($dataOrgs[$org->notes]))
                    {
                        $dataOrgs[$org->notes] = [];
                    }
                      
                    $dataOrgs[$org->notes][$org->id] = $org;
                    $bar->advance();
                }

                $toWrite = [];
                foreach ($dataOrgs as $key => $org)
                {
                    if (count($org) <= 1) continue;
                    $toWrite[$key] = $org;
                }

                File::put(public_path() . '/data/orgs/' . uniqid() . ".txt", json_encode($toWrite));
                $this->info($inc);
            });

        $bar->finish();
    }

    private function orgsMarkDelete()
    {
        $path = public_path() . "/data/orgs/";
        $files = File::allFiles($path);

        $inc = 0;
        $total = count($files);
        $bar = $this->output->createProgressBar($total);

        foreach ($files as $key => $file)
        {
            $inc += 1;
            // if ($key < 5) continue;

            $data = json_decode(File::get($file));
            if (empty($data)) continue;

            // dd($data);
            $test = [];

            // $nextDD = false;
            foreach ($data as $key => $item)
            {                
                $orgs = [];

                foreach ($item as $orgId => $org)
                {
                    $orgs[] = $org;
                }

                $toDelete = [];
                foreach ($orgs as $key => $org)
                {
                    if ($key == 0) continue;
                    $toDelete[] = $org->id;
                }

                // update organizations
                DB::table('organizations')->whereIn('id', $toDelete)->update(['notes' => 'delete']);
                // dd("DONE");
            }

            $this->info($total - $inc . " left");
            $bar->advance();
        }

        $bar->finish();
    }

    private function phoneCodes()
    {
        $this->line('Fixing phone codes...');

        $count = DB::table('phones')->where('type', 'work')->count();
        $bar = $this->output->createProgressBar($count);

        DB::table('phones')->where('type', 'work')->chunk(1000, function($phones) use (&$bar)
        {
            foreach ($phones as $phone)
            {
                if (strlen($phone->code_operator) == 5)
                {
                    if (substr($phone->code_operator, 0, 1) == "7")
                    {
                        if (substr($phone->code_operator, 0, 4) == "7172")
                        {
                            DB::table('phones')->where('id', $phone->id)->update(['code_operator' => '7172']);
                        }
                        else
                        {
                            DB::table('phones')->where('id', $phone->id)->update([
                                'type' => 'mobile',
                                'code_operator' => substr($phone->code_operator, 0, 3)
                            ]);
                        }
                    }
                }

                $bar->advance();
            }
        });
        
        $bar->finish();   
    }

    private function phoneNumbers()
    {
        $phones = DB::table('phones')->where('code_operator', 'fix')->get();
        $bar = $this->output->createProgressBar(count($phones));

        $code = '7172';

        foreach ($phones as $phone)
        {   
            // 8..., or short numbers
            if (substr($phone->number, 0, 2) != "+7")
            {
                DB::table('phones')->where('id', $phone->id)->update([
                    'code_country' => '',
                    'code_operator' => 'short_numb'
                ]);
            }
            else
            {
                // not work phone
                if (substr($phone->number, 2, 6) != $code)
                {
                    // dd([$phone->number, substr($phone->number, 5)]);

                    DB::table('phones')->where('id', $phone->id)->update([
                        'type' => $phone->type,
                        'code_operator' => substr($phone->number, 2, 5),
                        'number' => substr($phone->number, 5)
                    ]);
                }
                else
                {
                    DB::table('phones')->where('id', $phone->id)->update([
                        'type' => 'work',
                        'code_operator' => $code,
                        'number' => substr($phone->number, 6)
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
    }
}
