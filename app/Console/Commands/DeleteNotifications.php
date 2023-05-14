<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Console\Command;
use App\Models\UserNotification;

class DeleteNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications';

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
        $today = Carbon::today();
        $allNotifications = Notification::where('created_at', '<=', $today->subDays(7))->get();
        foreach ($allNotifications as $key => $notification) {
            $userNotifications = UserNotification::where('notification_id', $notification->id)->delete();
            $notification->delete();
        }
    }
}
