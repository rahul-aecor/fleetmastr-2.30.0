<?php
namespace App\Repositories;

use \Illuminate\Support\Facades\DB;
use App\Custom\Repositories\EloquentRepositoryAbstract;
use Auth;

class MessagesRepository extends EloquentRepositoryAbstract {

	public function __construct()
    {
        $authUserMessageRegions = Auth::user()->messageRegions->pluck('id')->toArray();
        $authUserMessageRegionIds = implode(",", $authUserMessageRegions);
    	$this->Database = DB::table('messages')
            ->join('users as sent_by', 'messages.sent_by', '=', 'sent_by.id')
            ->join(\DB::raw('(select message_id, count(message_recipients.id) as recipients_count from message_recipients join users on users.id = message_recipients.user_id where users.is_disabled=0 and user_region_id IN ('.$authUserMessageRegionIds.') group by message_id) as message_recipients'), 'messages.id', '=', 'message_recipients.message_id')
            ->leftjoin('templates', 'messages.template_id', '=', 'templates.id')
            ->where(function($query) {
                $query->where('is_private_message', '0');
                $query->orWhere(function($query) {
                    $query->where('is_private_message', '1');
                    $query->where('sent_by', Auth::user()->id);
                });
            })
            ->select('messages.id', 'messages.content', 'messages.template_name',
                'recipients_count', 'templates.deleted_at as template_deleted_at',
                \DB::raw("CONVERT_TZ(messages.sent_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'sent_at'"),
                'sent_by.first_name', 'sent_by.last_name', 'sent_by.email');

        $this->visibleColumns = [
            'messages.id', 'messages.content', 'messages.template_name', 'recipients_count',
            \DB::raw("CONVERT_TZ(messages.sent_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'sent_at'"),
            'sent_by.first_name', 'sent_by.last_name', 'sent_by.email'
        ];

        $this->orderBy = [['messages.sent_at', 'DESC']];
	}    
}
