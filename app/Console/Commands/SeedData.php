<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use File;
use App\Branch;

class SeedData extends Command
{
    // TODO: other cities
    protected $cities = [
        68 => 2,              // astana
        161 => 1,             // shymkent
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:data {cityname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // $this->prepRubricsRel();
        // return;

        $arguments = $this->argument();
        $cityname = $arguments['cityname'];

        DB::beginTransaction();

        // Step 0:
        $this->info("Working...");

        // Step 1:
        $time_operation_start = microtime(true);
        // $this->prepBranches($cityname);
        $time_operation_end = microtime(true);
        // $this->info('Done step 1/5 in ' . ($time_operation_end - $time_operation_start) . ' sec');
        $this->info("Skipping parsing branches... already done.");

        // Step 2:
        $time_operation_start = microtime(true);
        $this->prepOrgs($cityname);
        $time_operation_end = microtime(true);
        $this->info('Done step 2/5 in ' . ($time_operation_end - $time_operation_start) . ' sec');

        // Step 3:
        $time_operation_start = microtime(true);
        $this->insertOrgs($cityname);
        $time_operation_end = microtime(true);
        $this->info('Done step 3/5 in ' . ($time_operation_end - $time_operation_start) . ' sec');
        
        // Step 4:
        $time_operation_start = microtime(true);
        $this->prepOrgBranchRel($cityname);
        $time_operation_end = microtime(true);
        $this->info('Done step 4/5 in ' . ($time_operation_end - $time_operation_start) . ' sec');
        
        // Step 5:
        $time_operation_start = microtime(true);
        $this->insertBranches($cityname);
        $time_operation_end = microtime(true);
        $this->info('Done step 5/5 in ' . ($time_operation_end - $time_operation_start) . ' sec');

        DB::commit();        

        $time_end = microtime(true);
        $this->info("\n==========");
        $this->info("Done in " . ($time_end - $time_start) . " sec");
    }

    // prep {cityname} branches.json
    private function prepBranches($cityname)
    {
        $branchesDir = public_path() . '/data/branches/';
        $branches = File::files($branchesDir . '/' . $cityname);

        $orgs = [];
        $branchesTotal = [];

        foreach ($branches as $branch)
        {
            $data = json_decode(File::get($branch));
            $items = $data->result->items;

            $parsed = $this->parseBranches($items);
            
            foreach ($parsed['branches'] as $b)
            {
                $branchesTotal[] = $b;
            }
        }

        File::put(public_path() . '/data/parsed/' . $cityname . '/json_branches.js', json_encode($branchesTotal));
    }

    // prep orgs {cityname} orgs.json
    public function prepOrgs($cityname)
    {
        $file = public_path() . '/data/parsed/' . $cityname . '/json_branches.js';
        $items = json_decode(File::get($file));

        $orgKeys = [];
        $orgs = [];

        foreach ($items as $item)
        {
            if (in_array($item->org_id, $orgKeys)) continue;

            $orgs[] = [
                'id' => $item->org_id,
                'name' => $item->org_name
            ];

            $orgKeys[] = $item->org_id;

            // if (count($item->rubrics) > 2) return response()->json($item);
        }

        File::put(public_path() . '/data/parsed/' . $cityname . '/json_orgs.js', json_encode($orgs));
    }

    // insert orgs
    private function insertOrgs($cityname)
    {
        $orgsFile = File::get(public_path() . '/data/parsed/' . $cityname . '/json_orgs.js');
        $orgs = json_decode($orgsFile);

        $orgsWithIds = [];

        foreach ($orgs as $org) {
            $orgId = DB::table('organizations')->insertGetId([
                'name'          => $org->name,
                'description'   => '',
                'type'          => 'custom',
                'status'        => 'draft',
                'notes'          => $org->id
            ]);

            $orgsWithIds[] = [
                'id' => $org->id,
                'name' => $org->name,
                'db_id' => $orgId,
            ];
        }

        File::put(public_path() . '/data/parsed/' . $cityname . '/json_orgs_with_db_ids.js', json_encode($orgsWithIds));
    }

    // prep orgs and branches rel
    private function prepOrgBranchRel($cityname)
    {
        $data = File::get(public_path() . '/data/parsed/' . $cityname . '/json_branches.js');
        $branches = json_decode($data);

        $orgsFile = File::get(public_path() . '/data/parsed/' . $cityname . '/json_orgs_with_db_ids.js');
        $orgs = json_decode($orgsFile);

        $org_branch = [];

        foreach ($branches as $key => $branch) 
        {
            foreach ($orgs as $org)
            {
                if ($org->id == $branch->org_id)
                {
                    $org_branch[$this->bigintval($org->id)] = [
                        'org' => $org,
                        'branch' => $branch
                    ];
                }
            }
        }

        File::put(public_path() . '/data/parsed/' . $cityname . '/json_org_branch_relationship.js', json_encode($org_branch));
    }

    // insert branches
    private function insertBranches($cityname)
    {
        $data = File::get(public_path() . '/data/parsed/' . $cityname . '/json_branches.js');
        $branches = json_decode($data);

        $relFile = File::get(public_path() . '/data/parsed/' . $cityname . '/json_org_branch_relationship.js');
        $rels = json_decode($relFile);

        foreach ($branches as $branch) 
        {
            $orgDbId = $rels->{$branch->org_id}->org->db_id;

            // insert branch and get its id
            $branchId = $this->insertBranchAndGetId($branch, $orgDbId);

            // open hours
            $this->insertBranchOpenHours($branch, $branchId);

            // rubrics
            $this->insertBranchRubrics($branch, $branchId);

            // contacts & socials
            $this->insertBranchPhones($branch, $branchId);
        }
    }

    // insert branch and get its id
    private function insertBranchAndGetId($branch, $orgDbId)
    {
        $branchId = DB::table('branches')->insertGetId([
            'city_id'           => $branch->city_id,
            'organization_id'   => $orgDbId,
            'name'              => $branch->name,
            'type'              => $branch->type,
            'description'       => '',
            'address'           => $branch->address,
            'email'             => $branch->email,
            'lat'               => $branch->lat,
            'lng'               => $branch->lon,
            'status'            => 'draft',
            // TODO: timestamps
            // 'created_at'        => 'date',
            // 'updated_at'        => 'date:now'
        ]);

        return $branchId;
    }

    // insert open hours
    private function insertBranchOpenHours($item, $branchId)
    {
        $mon_start = $mon_end = $tue_start = $tue_end = $wed_start = $wed_end = $thu_start = $thu_end = $fri_start = $fri_end = $sat_start = $sat_end = $sun_start = $sun_end = null;
        $mon_dayoff = $mon_atc = $tue_dayoff = $tue_atc = $wed_dayoff = $wed_atc = $thu_dayoff = $thu_atc = $fri_dayoff = $fri_atc = $sat_dayoff = $sat_atc = $sun_dayoff = $sun_atc = 0;

        // no schedule? no open hours -> just skip
        if (!isset($item->schedule)) return;

        // prep open hours

        // Mon
        if (isset($item->schedule->Mon))
        {
            if (count($item->schedule->Mon->working_hours) == 1)
            {
                $hours = $item->schedule->Mon->working_hours[0];

                if ($hours->to == "24:00")
                {
                    $mon_atc = 1;
                }
                else
                {
                    $mon_start = $hours->from;
                    $mon_end = $hours->to;
                }
            }
            else
            {
                $hours = [0 => null, 1 => null];
                foreach ($item->schedule->Mon->working_hours as $k => $v)
                {
                    if ($k == 0) $hours[0] = $v;
                    else $hours[1] = $v;
                }
                
                $mon_start = $hours[0]->from;
                $mon_end = $hours[1]->to;
            }
        } 
        else 
        {
            $mon_dayoff = 1;
        }

        // Tue
        if (isset($item->schedule->Tue))
        {
            if (count($item->schedule->Tue->working_hours) == 1)
            {
                $hours = $item->schedule->Tue->working_hours[0];

                if ($hours->to == "24:00")
                {
                    $tue_atc = 1;
                }
                else
                {
                    $tue_start = $hours->from;
                    $tue_end = $hours->to;
                }
            }
            else
            {
                $hours = [0 => null, 1 => null];
                foreach ($item->schedule->Tue->working_hours as $k => $v)
                {
                    if ($k == 0) $hours[0] = $v;
                    else $hours[1] = $v;
                }
                
                $tue_start = $hours[0]->from;
                $tue_end = $hours[1]->to;
            }
        } 
        else 
        {
            $tue_dayoff = 1;
        }

        // Wed
        if (isset($item->schedule->Wed))
        {
            if (count($item->schedule->Wed->working_hours) == 1)
            {
                $hours = $item->schedule->Wed->working_hours[0];

                if ($hours->to == "24:00")
                {
                    $wed_atc = 1;
                }
                else
                {
                    $wed_start = $hours->from;
                    $wed_end = $hours->to;
                }
            }
            else
            {
                $hours = [0 => null, 1 => null];
                foreach ($item->schedule->Wed->working_hours as $k => $v)
                {
                    if ($k == 0) $hours[0] = $v;
                    else $hours[1] = $v;
                }
                
                $wed_start = $hours[0]->from;
                $wed_end = $hours[1]->to;
            }
        } 
        else 
        {
            $wed_dayoff = 1;
        }

        // Thu
        if (isset($item->schedule->Thu))
        {
            if (count($item->schedule->Thu->working_hours) == 1)
            {
                $hours = $item->schedule->Thu->working_hours[0];

                if ($hours->to == "24:00")
                {
                    $thu_atc = 1;
                }
                else
                {
                    $thu_start = $hours->from;
                    $thu_end = $hours->to;
                }
            }
            else
            {
                $hours = [0 => null, 1 => null];
                foreach ($item->schedule->Thu->working_hours as $k => $v)
                {
                    if ($k == 0) $hours[0] = $v;
                    else $hours[1] = $v;
                }
                
                $thu_start = $hours[0]->from;
                $thu_end = $hours[1]->to;
            }
        } 
        else 
        {
            $thu_dayoff = 1;
        }

        // Fri
        if (isset($item->schedule->Fri))
        {
            if (count($item->schedule->Fri->working_hours) == 1)
            {
                $hours = $item->schedule->Fri->working_hours[0];

                if ($hours->to == "24:00")
                {
                    $fri_atc = 1;
                }
                else
                {
                    $fri_start = $hours->from;
                    $fri_end = $hours->to;
                }
            }
            else
            {
                $hours = [0 => null, 1 => null];
                foreach ($item->schedule->Fri->working_hours as $k => $v)
                {
                    if ($k == 0) $hours[0] = $v;
                    else $hours[1] = $v;
                }
                
                $fri_start = $hours[0]->from;
                $fri_end = $hours[1]->to;
            }
        } 
        else 
        {
            $fri_dayoff = 1;
        }

        // Sat
        if (isset($item->schedule->Sat))
        {
            if (count($item->schedule->Sat->working_hours) == 1)
            {
                $hours = $item->schedule->Sat->working_hours[0];

                if ($hours->to == "24:00")
                {
                    $sat_atc = 1;
                }
                else
                {
                    $sat_start = $hours->from;
                    $sat_end = $hours->to;
                }
            }
            else
            {
                $hours = [0 => null, 1 => null];
                foreach ($item->schedule->Sat->working_hours as $k => $v)
                {
                    if ($k == 0) $hours[0] = $v;
                    else $hours[1] = $v;
                }
                
                $sat_start = $hours[0]->from;
                $sat_end = $hours[1]->to;
            }
        } 
        else 
        {
            $sat_dayoff = 1;
        }

        // Sun
        if (isset($item->schedule->Sun))
        {
            if (count($item->schedule->Sun->working_hours) == 1)
            {
                $hours = $item->schedule->Sun->working_hours[0];

                if ($hours->to == "24:00")
                {
                    $sun_atc = 1;
                }
                else
                {
                    $sun_start = $hours->from;
                    $sun_end = $hours->to;
                }
            }
            else
            {
                $hours = [0 => null, 1 => null];
                foreach ($item->schedule->Sun->working_hours as $k => $v)
                {
                    if ($k == 0) $hours[0] = $v;
                    else $hours[1] = $v;
                }
                
                $sun_start = $hours[0]->from;
                $sun_end = $hours[1]->to;
            }
        } 
        else 
        {
            $sun_dayoff = 1;
        }

        // insert to open_hours table
        DB::table('open_hours')->insert([
            'branch_id' => $branchId,
            'monday_start' => $mon_start,
            'monday_end' => $mon_end,
            'monday_atc' => $mon_atc,
            'monday_dayoff' => $mon_dayoff,
            'tuesday_start' => $tue_start,
            'tuesday_end' => $tue_end,
            'tuesday_atc' => $tue_atc,
            'tuesday_dayoff' => $tue_dayoff,
            'wednesday_start' => $wed_start,
            'wednesday_end' => $wed_end,
            'wednesday_atc' => $wed_atc,
            'wednesday_dayoff' => $wed_dayoff,
            'thursday_start' => $thu_start,
            'thursday_end' => $thu_end,
            'thursday_atc' => $thu_atc,
            'thursday_dayoff' => $thu_dayoff,
            'friday_start' => $fri_start,
            'friday_end' => $fri_end,
            'friday_atc' => $fri_atc,
            'friday_dayoff' => $fri_dayoff,
            'saturday_start' => $sat_start,
            'saturday_end' => $sat_end,
            'saturday_atc' => $sat_atc,
            'saturday_dayoff' => $sat_dayoff,
            'sunday_start' => $sun_start,
            'sunday_end' => $sun_end,
            'sunday_atc' => $sun_atc,
            'sunday_dayoff' => $sun_dayoff,
        ]);
    }

    // map with rubrics
    private function insertBranchRubrics($branch, $branchId) 
    {
        $relFile = File::get(public_path() . '/data/' . 'json_rubrics_rel.js');
        $rubricsRel = json_decode($relFile);

        $inserted = [];

        foreach ($branch->rubrics as $key => $rubric) 
        {
            if (!isset($rubricsRel->{str_replace('-', '_', str_slug($rubric->rubric_name))})) continue;
            
            $catId = $rubricsRel->{str_replace('-', '_', str_slug($rubric->rubric_name))};
            // dd($catId);

            if (isset($inserted[$catId])) continue;

            // map branch with category
            DB::table('branch_category')->insert([
                'branch_id' => $branchId,
                'category_id' => $catId
            ]);

            // set key already used
            $inserted[$catId] = $catId;
        }

        // dd("not found");
    }

    // phones
    private function insertBranchPhones($branch, $branchId)
    {
        foreach ($branch->contacts as $key => $contact)
        {
            if ($contact->type == 'phone') 
            {
                $phoneData = $this->getPhoneData($contact);

                DB::table('phones')->insert([
                    'branch_id' => $branchId,
                    'type' => $phoneData['type'],
                    'code_country' => $phoneData['country'],
                    'code_operator' => $phoneData['operator'],
                    'number' => $phoneData['number']
                ]);
            }
            else
            {
                $this->insertBranchSocial($contact, $branchId);
            }
        }
    }

    private function insertBranchSocial($contact, $branchId)
    {
        $type = $name = '';

        // only website, facebook & instagram
        if (!in_array($contact->type, ['website', 'instagram', 'facebook'])) return;

        if ($contact->type == "website")
        {
            $name = mb_substr($contact->value, mb_strpos($contact->value, "?") + 1);
        }
        else
        {
            $name = $contact->value;
        }

        DB::table('socials')->insert([
            'branch_id' => $branchId,
            'type' => $contact->type,
            'name' => $name
        ]);
    }

    private function getPhoneData($contact)
    {
        $type = $country = $operator = $number = '';

        if (!starts_with($contact->text, '+7'))
          {
            $type = 'short_numb';
            $country = '';
            $operator = 'short_numb';
            $number = $contact->value;
          }
          else
          {
            $type = str_contains($contact->text, '(') ? 'work' : 'mobile';
            
            if ($type == 'mobile')
            {
                $indexFirstDash = mb_strpos($contact->text, '‒');
                $indexSecondDash = mb_strpos($contact->text, '‒', $indexFirstDash + 1);
                $country = mb_substr($contact->text, 0, $indexFirstDash);
                $operator = mb_substr($contact->text, $indexFirstDash + 1, $indexSecondDash - $indexFirstDash - 1);
                $number = preg_replace("/[^0-9]/", "", mb_substr($contact->text, $indexSecondDash + 1));
            }
            else
            {
                $indexOpenBracket = mb_strpos($contact->text, '(');
                $indexCloseBracket = mb_strpos($contact->text, ')');
                $country = preg_replace("/[^0-9+]/", "", mb_substr($contact->text, 0, $indexOpenBracket));
                $operator = mb_substr($contact->text, $indexOpenBracket + 1, $indexCloseBracket - $indexOpenBracket - 1);
                $number = preg_replace("/[^0-9]/", "", mb_substr($contact->text, $indexCloseBracket + 1));
            }
        }

        $data = [
            'type' => $type,
            'country' => $country,
            'operator' => $operator,
            'number' => $number
        ];

        return $data;
    }

    // prep rubrics rel
    private function prepRubricsRel()
    {
        $map = [];
        $rubrics = DB::table('tmp_category_mapper')->get();

        foreach ($rubrics as $rubric)
        {
            if ($rubric->cat_id == 210) $rubric->cat_id = 226;
            if ($rubric->cat_id == 211) $rubric->cat_id = 232;
            if ($rubric->cat_id == 212) $rubric->cat_id = 233;
            if ($rubric->cat_id == 213) $rubric->cat_id = 234;
            if ($rubric->cat_id == 214) $rubric->cat_id = 235;
            if ($rubric->cat_id == 215) $rubric->cat_id = 236;
            if ($rubric->cat_id == 216) $rubric->cat_id = 237;
            if ($rubric->cat_id == 217) $rubric->cat_id = 238;
            if ($rubric->cat_id == 218) $rubric->cat_id = 239;
            if ($rubric->cat_id == 219) $rubric->cat_id = 240;
            if ($rubric->cat_id == 220) $rubric->cat_id = 241;
            if ($rubric->cat_id == 221) $rubric->cat_id = 242;

            $map[str_replace('-', "_", str_slug($rubric->name))] = $rubric->cat_id;
        }

        File::put(public_path() . '/data/json_rubrics_rel.js', json_encode($map));
    }

    // parse branches
    public function parseBranches($branches)
    {
        $parsed = [
            'org' => null,
            'branches' => [],
            'gis' => null
        ];

        // dd("Parse");
        foreach ($branches as $branch)
        {
            $email = '';
            $contacts = $phones = $socials = [];

            $parsed['gis'] = $branch;

            // branch
            $type = (count($parsed['branches']) > 0) ? 'custom' : 'main';
            $newBranch = [
                'org_id' => $branch->org->id,
                'org_name' => $branch->org->name,
                'city_id' => $this->cities[$branch->region_id],
                'name' => $branch->name,
                'type' => $type,
                'description' => '',
                'address' => isset($branch->address_name) ? $branch->address_name : '',
                'post_index' => '',
                'email' => $email,
                'lat' => isset($branch->point) ? $branch->point->lat : '0.00',
                'lon' => isset($branch->point) ? $branch->point->lon : '0.00',
                'rubrics' => [],
                'contacts' => [],
                'socials' => []
              ];

            if (isset($branch->rubrics))
            {
                foreach ($branch->rubrics as $rubric)
                {
                    $newBranch['rubrics'][] = [
                        'rubric_id' => $rubric->id,
                        'rubric_name' => $rubric->name
                    ];
                }
            }

            // schedule
            $newBranch['schedule'] = $branch->schedule;

            // phones, websites, socials
            if (isset($branch->contact_groups))
            {
                foreach ($branch->contact_groups as $contactGroup)
                {
                    foreach ($contactGroup->contacts as $contact)
                    {
                        $newBranch['contacts'][] = $contact;
                    }
                }
            }

            // email
            foreach ($newBranch['contacts'] as $contact) {
                if ($contact->type == 'email') {
                    $newBranch['email'] = $contact->value;
                    break;
                }
            }

            $parsed['branches'][] = $newBranch;
        }

        return $parsed;
    }

    private function bigintval($value) 
    {
        $value = trim($value);
        if (ctype_digit($value)) return $value;

        $value = preg_replace("/[^0-9](.*)$/", '', $value);
        if (ctype_digit($value)) return $value;

        return 0;
    }
}
