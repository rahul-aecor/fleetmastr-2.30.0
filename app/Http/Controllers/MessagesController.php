<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use View;
use Input;
use JavaScript;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Group;
use App\Models\UserRegion;
use App\Http\Requests;
use App\Models\Message;
use App\Models\UserDivision;
use Illuminate\Http\Request;
use App\Custom\Helper\Common;
use App\Models\MessageRecipient;
use App\Http\Controllers\Controller;
use App\Repositories\MessagesRepository;
use App\Contracts\UserNotificationService;
use App\Custom\Facades\GridEncoder;

class MessagesController extends Controller
{
    public $title= 'Messaging';

    public function __construct(UserNotificationService $notification) 
    {
        $this->notification = $notification;
        View::share ( 'title', $this->title );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $messagesCount = [];
        $messagesCount['today'] = $this->getMessageCount('today');
        $messagesCount['yesterday'] = $this->getMessageCount('yesterday');
        $messagesCount['all_time'] = $this->getMessageCount('all_time');
        $messagesCount['this_year'] = $this->getMessageCount('this_year');
        $messagesCount['this_month'] = $this->getMessageCount('this_month');
        $messagesCount['last_month'] = $this->getMessageCount('last_month');

        JavaScript::put([
            'singleFileSize' => config('config-variables.message_attachments.single_file_size'),
            'totalFileSize' => config('config-variables.message_attachments.total_file_size'),
            'attachmentDefaultMessage' => config('config-variables.message_attachments.default_message')
        ]);

        return view('messages.index', compact('messagesCount'));
    }

    private function getMessageCount($type)
    {
        $authUserMessageRegions = Message::getAuthUserMessagePermissionUsers();
        $messageRecipients = MessageRecipient::join('messages', 'message_id', '=', 'messages.id')
                                    ->where(function($query) {
                                        $query->where('is_private_message', '0');
                                        $query->orWhere(function($query) {
                                            $query->where('is_private_message', '1');
                                            $query->where('sent_by', Auth::user()->id);
                                        });
                                    })->whereIn('user_id', $authUserMessageRegions);

        if($type == 'today') {
            $messageRecipients = $messageRecipients->whereDate('message_recipients.created_at', '=', Carbon::today()->toDateString());
        } else if($type == 'yesterday') {
            $messageRecipients = $messageRecipients->whereDate('message_recipients.created_at', '=', Carbon::yesterday()->toDateString());
        } else if($type == 'all_time') {
            $messageRecipients = $messageRecipients;
        } else if($type == 'this_year') {
            $messageRecipients = $messageRecipients->whereYear('message_recipients.created_at', '=', date('Y'));
        } else if($type == 'this_month') {
            $messageRecipients = $messageRecipients->whereMonth('message_recipients.created_at', '=', Carbon::now()->month)->whereYear('message_recipients.created_at', '=', date('Y'));
        } else {
            $lastMonth = Carbon::now()->subMonth()->month;
            $lastMonthsYear = Carbon::now()->subMonth()->year;
            $messageRecipients = $messageRecipients->whereMonth('message_recipients.created_at', '=', $lastMonth)->whereYear('message_recipients.created_at', '=', $lastMonthsYear);
        }

        return $messageRecipients->count();
    }

    public function paginate()
    {
        $messages = Message::with('sender', 'receiver', 'group')->orderBy('sent_at', 'desc')->paginate(10);
        return $messages;
    }    

    /**
     * @return [type]
     */
    public function anyData()
    {
        return GridEncoder::encodeRequestedData(new MessagesRepository(), Input::all());
    }

    /**
     * Get message recipient.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMessageRecipient($message_id)
    {
        $authUserMessageRegions = Auth::user()->messageRegions->pluck('id');
        $message = Message::with(['sender', 'receiver' => function($query) use($authUserMessageRegions) {
                $query->leftjoin('users', 'message_recipients.user_id', '=', 'users.id')
                        ->leftjoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                        ->leftjoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                        ->whereIn('user_region_id', $authUserMessageRegions)
                        ->select('message_recipients.*', 'user_divisions.name as division_name', 'users.user_division_id', 'user_regions.name as region_name', 'users.user_region_id', 'users.engineer_id');
            }])
            ->where('id', $message_id)
            ->first();

        $numberofreceipients = $message->receiver->count();
        $numberOfResponseReceived = 0;
        foreach($message->receiver as $key => $val){
            if ($val->response_received_at != null) {
                $numberOfResponseReceived++;
            }
        }
        $percent = $numberofreceipients == 0 ? 0 : number_format((float)($numberOfResponseReceived*100)/$numberofreceipients, 2, '.', '');

        return view('messages.details', compact('message', 'sent_date', 'percent'));
    }

    /**
     * create and send report.
     *
     * @return \Illuminate\Http\Response
     */
    public function report($message_id)
    {
        $message = Message::findOrFail($message_id);
        $excelFileDetail=array(
            'title' => "Message Report"
            );

        $sheetArray=[];

        $sheet=[];
        $sheet['autofilter'] = 'no';
        $sheet['labelArray'] = [
            'Date sent', 'Sender', 'Template name', 'Message', 'Recipient name', 'Recipient number', 'Message status', 'Response received'
        ];
        $questionCounter=1;
        $numberofquestions=0;

        $sheet['dataArray'] = [];
        $deliveredMessageCount = 0;
        $responseReceivedCount = 0;
        $centPersentScorers = 0;
        $responseReceivedStr = "none";
        $userIdArr = Message::getAuthUserMessagePermissionUsers();
        $message_recipients = MessageRecipient::where('message_id', $message_id)
                                            ->whereNotNull('response')
                                            ->whereIn('user_id', $userIdArr)
                                            ->with(['message.sender','message'])
                                            ->get();

        $sheet['charts'] = [];
        $chartcounter = 1;
        $sheet['otherParams'] = [
            'sheetName' => "Message_Report"
        ];
        $sheet['columnFormat'] = [];

        if (!$message_recipients->isEmpty()) {
            $firstResp = json_decode($message_recipients->first()->response);
            if($message->type == 'multiple_choice'){
                $questionsArray = $firstResp->questions;            
                foreach ($questionsArray as $key => $value) {
                    array_push($sheet['labelArray'], 'Question'.$questionCounter);
                    array_push($sheet['labelArray'], 'Answer'.$questionCounter);
                    array_push($sheet['labelArray'], 'Result');
                    $questionCounter++;
                }
                $numberofquestions = $questionCounter - 1 ;

                array_push($sheet['labelArray'], 'Total score');
                array_push($sheet['labelArray'], '%');
            }
            if($message->type == 'survey'){
                $questionsArray = $firstResp->surveys;            
                foreach ($questionsArray as $key => $value) {
                    array_push($sheet['labelArray'], 'Question'.$questionCounter);
                    array_push($sheet['labelArray'], 'Answer'.$questionCounter);
                    $questionCounter++;
                }
                $numberofquestions = $questionCounter - 1 ;
            }

            //if (!$message_recipients->isEmpty()) {
                foreach ($message_recipients as $recipient) {
                    $totalscore = 0;
                    $respArray = json_decode($recipient->response);
                    if($recipient->status == 'delivered' || $recipient->status == 'read'){
                        $deliveredMessageCount++;
                    }
                    if ($recipient->response_received_at != null) {                    
                        $responseReceivedStr = Carbon::parse($recipient->response_received_at)->setTimezone(config('config-variables.format.displayTimezone'))->format(config('config-variables.format.showDateTime'));
                        $responseReceivedCount++;
                    }
                    $data=[
                        'dateSent' => $recipient->message->sent_at,
                        'sender' => $recipient->message->sender->email,
                        //'templateName' => $recipient->message->template_name,
                        //'message' => $recipient->message->content,
                        'templateName' => $respArray->template_name,
                        'message' => $respArray->content,
                        'recipientName' => $recipient->name,
                        'recipientNumber' => $recipient->mobile,
                        'messageStatus' => ucfirst($recipient->status),
                        'responseReceived' => $responseReceivedStr //$recipient->response_received_at
                    ];
                    $processArray = [];
                    if($message->type == 'multiple_choice'){
                        if (isset($respArray->questions)) {
                            $processArray = $respArray->questions;
                        }
                        $selectLabel = 'question';
                    }
                    if($message->type == 'survey'){
                        if(isset($respArray->surveys)){
                            $processArray = $respArray->surveys;
                        }
                        $selectLabel = 'text';
                    }

                    if (!empty($processArray)) {
                        foreach ($processArray as $questionInResp) {
                            array_push($data, $questionInResp->$selectLabel);
                            if($message->type == 'multiple_choice'){
                                foreach ($questionInResp->answers as $answerInResp) {
                                    if (isset($answerInResp->is_selected) && $answerInResp->is_selected == 'true') {
                                        array_push($data, $answerInResp->text);
                                        if ($answerInResp->is_correct == 'true') {
                                            array_push($data, 1);
                                            $totalscore++;
                                        }
                                        else{
                                            array_push($data, 0);
                                        }
                                    }
                                }

                            }
                            if($message->type == 'survey'){
                                array_push($data, $questionInResp->answer);
                            }
                            //array_push($data, $questionInResp->answers);
                        }
                        if($message->type == 'multiple_choice'){
                            array_push($data, $totalscore);
                            array_push($data, number_format((float)($totalscore*100)/$numberofquestions));
                            //array_push($data, ($totalscore*100)/$numberofquestions);
                            if($totalscore == $numberofquestions){
                                $centPersentScorers++;
                            }
                        }

                        array_push($sheet['dataArray'], $data);
                    }
                }
            //}

            $numberOfColumns = sizeof($sheet['labelArray']);
            $sheet['summaryRow'] = ["","","","TOTAL",$message_recipients->count(),"",$deliveredMessageCount,$responseReceivedCount];
            if($message->type == 'multiple_choice'){
                $result = array_fill(0,$numberofquestions,0);
                foreach ($sheet['dataArray'] as $row) {
                    $resultCounter = 0;
                    $keys = array_keys($row);
                    $columnNumber = 10;//from this column result starts;
                    while($resultCounter < $numberofquestions){
                        $result[$resultCounter] = $result[$resultCounter] + $row[$keys[$columnNumber]];
                        $columnNumber = $columnNumber + 3;
                        $resultCounter++;
                    }          
                }
                $columnNumber = 10;//from this column result starts;
                $resultIterator = 0;
                for ($counter=8; $counter < $numberOfColumns; $counter++) {             
                    if ($counter == $columnNumber) {
                        array_push($sheet['summaryRow'],$result[$resultIterator]);                       
                        $columnNumber = $columnNumber + 3;

                        //setting chart data
                        $chartdata['dataseriesLabels'] = ['Result'.$chartcounter];
                        $chartdata['xAxisTickValues'] = ['Correct', 'Incorrect'];
                        $chartdata['dataSeriesValues'] = [$result[$resultIterator],$message_recipients->count()-$result[$resultIterator]];
                        $chartdata['title'] = "Question".$chartcounter." Breakdown";
                        array_push($sheet['charts'], $chartdata);

                        $resultIterator++;
                        $chartcounter++;
                    }
                    else{
                        array_push($sheet['summaryRow'],'N/A');
                    }                      
                }
            }

        }

        if($message->type == 'multiple_choice'){
            //setting chart data
            $chartdata['dataseriesLabels'] = ['Result'.$chartcounter];
            $chartdata['xAxisTickValues'] = ['100% correct', 'Below 100% correct'];
            $chartdata['dataSeriesValues'] = [$centPersentScorers, $message_recipients->count()-$centPersentScorers];
            $chartdata['title'] = "Total Responders Who Achieved 100%";
            array_push($sheet['charts'], $chartdata);
        }

        array_push($sheetArray, $sheet);
        $commonHelper = new Common();
        $exportFile= $commonHelper->downloadDesktopExcel($excelFileDetail, $sheetArray, 'xlsx', 'yes');
    }    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $users = $data['template']['users'];
        $numbers = trim($data['numbers'], ";");
        $group_ids = array_pluck($data['template']['groups'], 'id');
        $ad_hoc_numbers = [];

        $group_users = Group::extractRecipients($group_ids);
        $division_users = [];
        if(isset($data['template']['userdivisions'])) {
            $division_users = array_pluck($data['template']['userdivisions'], 'id');
            // $division_users = UserDivision::extractRecipients($division_users);
            $division_users = UserRegion::extractRecipients($division_users);
        }
        
        $users = array_merge($users, $group_users, $division_users);

        $this->notification->setRequest($request);
        $message = $this->notification->toUsers($users)
            ->toNumbers($ad_hoc_numbers)
            ->send();    
        
        return $message;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Get message content.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMessageContent($message_id)
    {
        $message = Message::where('id', $message_id)
                            ->first();

        return view('messages.content', compact('message'));
    }

    /**
     * Download message status report.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadMessageStatusReport($message_id)
    {
        $authUserMessageRegions = Auth::user()->messageRegions->pluck('id');
        $message = Message::with(['sender', 'receiver' => function($query) use($authUserMessageRegions) {
                $query->leftjoin('users', 'message_recipients.user_id', '=', 'users.id')
                        ->leftjoin('user_divisions', 'users.user_division_id', '=', 'user_divisions.id')
                        ->leftjoin('user_regions', 'users.user_region_id', '=', 'user_regions.id')
                        ->whereIn('user_region_id', $authUserMessageRegions)
                        ->select('message_recipients.*', 'user_divisions.name as division_name', 'users.user_division_id', 'user_regions.name as region_name', 'users.user_region_id', 'users.engineer_id');
            }])
            ->where('id', $message_id)
            ->first();

        $excelFileDetail=array(
            'title' => "Message Status"
            );

        $sheetArray=[];

        $sheet=[];
        $sheet['autofilter'] = 'no';
        $sheet['labelArray'] = [
            'Name', 'Division', 'Region', 'ID', 'Send Channel', 'Status', 'Response/Acknowledgement Received'
        ];
        $sheet['otherParams'] = [
            'sheetName' => "Message_Status"
        ];
        $sheet['columnFormat'] = [];
        $sheet['dataArray'] = [];

        foreach($message->receiver as $key => $val) {
            $sendChannel = null;
            $responseReceivedAt = null;

            if($val->sent_via == 'sms') {
                $sendChannel = $val->mobile ? $val->mobile : '-';
            } else {
                $sendChannel = ucfirst($val->sent_via);
            }

            if($val->response_received_at != null) {
                $responseReceivedAt = $val->response_received_at->setTimezone(config('config-variables.format.displayTimezone'))->format(config('config-variables.format.showDateTime'));
            } else {
                $responseReceivedAt = 'None';
            }

            $data = [
                'name' => $val->name,
                'division' => $val->division_name ? $val->division_name : '',
                'region' => $val->region_name ? $val->region_name : '',
                'id' => $val->engineer_id ? $val->engineer_id : '',
                'sendChannel' => $sendChannel,
                'status' => ucfirst($val->status),
                'response/AcknowledgementReceived' => $responseReceivedAt,
            ];
            array_push($sheet['dataArray'], $data);
        }

        array_push($sheetArray, $sheet);
        $commonHelper = new Common();
        $exportFile= $commonHelper->downloadDesktopExcel($excelFileDetail, $sheetArray, 'xlsx', 'yes');
    }
}
