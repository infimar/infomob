<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;


class PrepDatabase extends Command
{
    protected $cities = [
        'shymkent' => 1
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prep:database {cityname}';

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
        $arguments = $this->argument();
        $cityname = $arguments['cityname'];

        // first find orgs
        $subscriptions = DB::table('subscriptions')
            ->get();

        $orgsToSave = [];
        foreach ($subscriptions as $subscription)
        {
            $orgsToSave[] = $subscription->organization_id;
        }

        $branches = DB::table('branches')
            ->where('city_id', $this->cities[$cityname])
            ->where('subscription', 'none')
            ->whereNotIn('organization_id', $orgsToSave)
            ->get(['id', 'organization_id']);

        $orgsToDelete = [];
        $branchesToDelete = [];

        foreach ($branches as $branch)
        {
            $orgsToDelete[$branch->organization_id] = $branch->organization_id;
            $branchesToDelete[$branch->id] = $branch->id;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::beginTransaction();

        // remove orgs
        DB::table('organizations')->whereIn('id', $orgsToDelete)->delete();

        // remove open_hours
        DB::table('open_hours')->whereIn('branch_id', $branchesToDelete)->delete();

        // remove branch_category
        DB::table('branch_category')->whereIn('branch_id', $branchesToDelete)->delete();
        
        // remove phones
        DB::table('phones')->whereIn('branch_id', $branchesToDelete)->delete();
        
        // remove socials
        DB::table('socials')->whereIn('branch_id', $branchesToDelete)->delete();
        
        // remove branches
        DB::table('branches')->whereIn('id', $branchesToDelete)->delete();

        DB::commit();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Done: ' . count($orgsToDelete));
    }
}
