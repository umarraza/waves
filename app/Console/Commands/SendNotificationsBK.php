<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduleSessions;
use App\Models\ScheduleSessions2;
use App\Models\ScheduleAlert;
use App\HaseebModel;

class SendNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sent:notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sent Notifications to mobile devices';

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
        //Test Cron Jobs
        // $new = new HaseebModel();
        // $new->fid = 1;
        // $new->name = 'test';
        // $new->comment = 'test message';
        // $new->save();
        ScheduleSessions2::sentNotifications();
        ScheduleAlert::sentNotifications();
        ScheduleSessions::sentNotifications();

    }
}
