<?php

namespace App\Console\Commands;

use File;
use Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserRegion;
use Illuminate\Console\Command;
use App\Models\UserVerification;

class UserSendInvite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:send-invite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to send invites to the Users';

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
        $userRegions = UserRegion::whereIn('name', ['440','700','800'])->lists('id')->toArray();
        // print_r($userRegions);

        // $users = User::whereIn('user_region_id', $userRegions)->toSql();
        // dd($users);

        // $users = User::whereIn('user_region_id', $userRegions)->get();
        $users = User::whereIn('user_region_id', $userRegions)->take(1)->get();

        foreach ($users as $user) {
            $token = str_random(30);
            $link = url('users/verification', [$token]);
            $userVerification = new UserVerification();
            $userVerification->user_id = $user->id;
            $userVerification->key = $token;
            $userVerification->save();

            $userName = $user->first_name;
            // $emailAddress = $user->email;
            $emailAddress = "ndeopura@aecordigital.com";

            Mail::queue('emails.user_set_password', ['userName' => $userName, 'emailAddress' => $emailAddress, 'link' => $link], function ($message) use ($userName, $emailAddress, $token) {
                $message->to($emailAddress);
                $message->subject('fleetmastr - set your account password');
            });
        }
    }
}
