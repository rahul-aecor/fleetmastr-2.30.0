<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

  /**
   * Get user's unread notifications
   *
   * @param  View  $view
   * @return void
   */
  public static function getUserUnreadNotifications()
  {
  	$notifications = self::all();
  	$loggedInUser = Auth::user();

  	$fetchUserMsg = UserNotification::where(['user_id' => $loggedInUser->id])->get();
  	if (! empty($fetchUserMsg[0])) {
        $readNotifications = explode(',', $fetchUserMsg[0]['read_notification_id']);
        $deletedNotifications = explode(',', $fetchUserMsg[0]['deleted_notification_id']);
    }

    foreach ($notifications as $key => $val) {
        if (! empty($readNotifications) && in_array($val['id'], $readNotifications)) {
            unset($notifications[$key]);
        }

        if (! empty($deletedNotifications) && in_array($val['id'], $deletedNotifications)) {
            unset($notifications[$key]);
        }
    }

    return $notifications->count();
  }

  public static function getUserAllNotifications()
  {
    $notifications = self::all();

    $fetchUserNotifications = UserNotification::where(['user_id' => Auth::user()->id])->get();
    if (! empty($fetchUserNotifications[0])) {
        $readNotifications = explode(',', $fetchUserNotifications[0]['read_notification_id']);
        $deletedNotifications = explode(',', $fetchUserNotifications[0]['deleted_notification_id']);
    }

    foreach ($notifications as $key => $val) {
      if (! empty($readNotifications) && in_array($val['id'], $readNotifications)) {
          $notifications[$key]['read_notification'] = 1;
      } else {
          $notifications[$key]['read_notification'] = 0;
      }

      if (! empty($deletedNotifications) && in_array($val['id'], $deletedNotifications)) {
        unset($notifications[$key]);
      }      
    }

    return $notifications;
  }
}
