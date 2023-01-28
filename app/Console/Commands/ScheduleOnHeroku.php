<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ScheduleOnHeroku extends Command
{
    protected $signature = 'schedule:heroku';

    protected $description = 'Schedule bash on Heroku';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        while(true) {
            Artisan::call('schedule:run');
            sleep(60);
        }
    }
}
