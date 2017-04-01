<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;
use DB;
use SplFileObject;

class RemoveDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:duplicates';

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
        $fileOrgs = new SplFileObject(public_path() . '/data/remove_orgs.txt');
        $fileBranches = new SplFileObject(public_path() . '/data/remove_branches.txt');

        $orgsToDelete = $branchesToDelete = [];

        while (!$fileOrgs->eof()) {
            $orgsToDelete[] = $fileOrgs->fgets();
        }

        while (!$fileBranches->eof()) {
            $branchesToDelete[] = $fileBranches->fgets();
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
