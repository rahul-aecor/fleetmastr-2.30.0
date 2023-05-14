<?php

namespace App\Console\Commands\Onetime;

use Illuminate\Console\Command;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceHistory;
use App\Models\MaintenanceEvents;
use Carbon\Carbon;
use App\Custom\Helper\Common;
use Mail;

class FetchIncompleteMaintenanceEventsDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:incomplete-maintenance-events-details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch incomplete maintenance events details and send in email to Richard and Johan';

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
        $maintenanceEvents = MaintenanceEvents::get()->lists('name', 'id');
        $vehicleMaintenanceHistory = VehicleMaintenanceHistory::with('vehicle')->where('event_status', 'Incomplete')
                                                                ->orderBy('event_plan_date')
                                                                ->get();

        $dataArray = [];
        foreach($vehicleMaintenanceHistory as $event) {
            $entry = [];
            $entry[] = $event->vehicle->registration;
            $entry[] = $maintenanceEvents[$event->event_type_id];
            $entry[] = $event->event_plan_date;
            $entry[] = $event->event_date;
            $entry[] = $event->comment;
            $files = $event->getMedia();
            if(isset($files[0])) {
                foreach($files as $key => $value) {
                    if ($value->hasCustomProperty('caption') && !empty($value->custom_properties['caption'])) {
                        $name = $value->custom_properties['caption'] .".".pathinfo($value->file_name, PATHINFO_EXTENSION);
                    } else {
                        $name = $value->name.".".pathinfo($value->file_name, PATHINFO_EXTENSION);
                    }
                    $created = $value->created_at->format('H:i:s d M Y');
                    $size = $value->humanReadableSize;
                    $url = getPresignedUrl($value);
                    $entry[] = $name.', '.$created.', '.$size.', '.$url;
                }
            }
            array_push($dataArray, $entry);
        }

        $sheetArray = [];
        $sheet = [];
        $sheet['labelArray'] = ['Registration', 'Event', 'Planned Date' ,'Event Date', 'Comment', 'Documents(Name, Created, Size, URL)'];
        $sheet['dataArray'] = $dataArray;
        $repDate = Carbon::now()->startOfDay()->format('D jS F Y');
        $sheet['otherParams'] = ['sheetName' => "Vehicles Incomplete Entries"];
        $sheet['columnFormat'] = [];
        array_push($sheetArray, $sheet);

        $excelFileDetail=array( "title" => "Vehicles Incomplete Entries - " . $repDate );
        $commonHelper = new Common();
        $exportFile = $commonHelper->toExcel($excelFileDetail, $sheetArray, 'xlsx', 'no');

        Mail::send('emails.vehicle_incomplete_entries', [], function ($message) use($exportFile, $repDate) {
            $message->to(['jhaynes@imastr.com', 'rstenson@imastr.com', 'fdaudiya@aecordigital.com', 'mupadhyay@aecordigital.com']);
            $message->subject(strtoupper(env('BRAND_NAME')) . ' - Vehicle - Incomplete Entries Data');
            $message->attach($exportFile, ['as' => 'Vehicle Incomplete Entries Data - ' . $repDate]);
        });

    }
}
