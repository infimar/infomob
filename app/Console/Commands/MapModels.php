<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class MapModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'map:models';

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
        $mapper = [
            3 => 'gas_station',
            112 => 'drugs',
            232 => 'atm',
            85 => 'eat',
            87 => 'eat',
            93 => 'eat',
            210 => 'eat',
            88 => 'bar',
            188 => 'hotel'
        ];

        DB::beginTransaction();

        foreach ($mapper as $catId => $model)
        {
            $branchIds = [];

            $rel = DB::table('branch_category')->where('category_id', $catId)->get();
            foreach ($rel as $item)
            {
                $branchIds[] = $item->branch_id;
            }

            DB::table('branches')->whereIn('id', $branchIds)->update(['model' => $model]);
            $this->info($model . '... done.');
        }

        DB::commit();
        $this->info('Completed.');
    }
}
