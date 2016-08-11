<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use File;

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
        $action = $this->argument('name');

        switch ($action) 
        {
            case 'phones':
                $this->phones();
                break;
            
            default:
                $this->info("Wrong action name");
                break;
        }

        $this->info("Done");
    }

    private function phones()
    {
        $phones = DB::table('phones')->where('code_operator', 'fix')->get();
        $bar = $this->output->createProgressBar(count($phones));

        $code = '727';

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
                // work phone
                if (substr($phone->number, 2, 5) != $code)
                {
                    // dd([$phone->number, substr($phone->number, 5)]);

                    DB::table('phones')->where('id', $phone->id)->update([
                        'type' => $phone->type,
                        'code_operator' => $code,
                        'number' => substr($phone->number, 5)
                    ]);
                }
                else
                {
                    DB::table('phones')->where('id', $phone->id)->update([
                        'type' => 'work',
                        'code_operator' => substr($phone->number, 2, 5),
                        'number' => substr($phone->number, 5)
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
    }
}
