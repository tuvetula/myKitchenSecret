<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'migrate refresh, install laravel passport and set .env variables';

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
     * Execute the console command
     */
    public function handle()
    {
        $this->output->title($this->signature.' start');
        if(env('APP_ENV') != 'PROD' && $this->confirm('Are you sure you want to refresh database with migrations?'))
        {
            $this->info('migrate:refresh start');
            $this->callSilent('migrate:refresh');
            $this->info('migrate:refresh finished');

            $this->info('db:seed start');
            $this->output->progressStart(count(DatabaseSeeder::SEEDER_TO_CALL));
            foreach (DatabaseSeeder::SEEDER_TO_CALL as $seed){
                $this->callSilent('db:seed',[
                    '--class' => $seed
                ]);
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
            $this->info('db:seed finished');

            $this->info('passport:install start');
            $this->callSilently('passport:install');
            $personalAccessClientSecret = DB::table('oauth_clients')
                ->where('id',1)
                ->value('secret');
            $this->setEnv('PERSONNAL_ACCESS_CLIENT_SECRET',$personalAccessClientSecret);

            $grandClientSecret = DB::table('oauth_clients')
                ->where('id',2)
                ->value('secret');
            $this->setEnv('GRAND_CLIENT_SECRET',$grandClientSecret);
            $this->info('passport:install finished');
            $this->output->success($this->signature.' finished');
            $this->call('serve');
        } else {
            $this->info('command '.$this->signature.' execution has been canceled');
        }
    }

    private function setEnv($key, $value)
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key . '=' . env($key),
            $key . '=' . $value,
            file_get_contents(app()->environmentFilePath())
        ));
    }
}
