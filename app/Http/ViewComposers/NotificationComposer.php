<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\UserNotification;

class NotificationComposer
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(Request $request)
  {
    $this->request = $request;
  }

  /**
   * Bind data to the view.
   *
   * @param  View  $view
   * @return void
   */
  public function compose(View $view)
  {
  	$notification = Notification::leftjoin('user_notifications', 'notifications.id', '=', 'user_notifications.notification_id')
                                  ->where('user_id', Auth::user()->id)
                                  ->where('is_deleted', 0)
                                  ->orderBy('notifications.created_at', 'desc')
                                  ->get();

  	$notification_details = [];
    $notification_details['allNotification'] = $notification;
	  $notification_details['unReadNotificationCount'] = UserNotification::where('is_read', 0)->where('is_deleted', 0)
                  ->where('user_id', Auth::user()->id)->count();

  	$view->with('notification_details', $notification_details);
  }
}