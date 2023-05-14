<?php

namespace App\Handlers\Events;

use App\Events;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;
use Carbon\Carbon;


class AuthLoginEventHandler
{
    /**
     * Create the event handler.
     *
     * @return void
     */
    public function __construct()
    {
        
        // echo "<pre>";print_r();echo "</pre>";exit;
    }

    /**
     * Handle the event.
     *
     * @param  Events  $event
     * @return void
     */
    public function handle(User $user, $remember)
    {
        // $user = \Auth::user();
        if($user->isAppUser()){
            \Auth::logout();
        }
        else{
            $user->last_login = Carbon::now();
            $user->save();
        }
    }
}
