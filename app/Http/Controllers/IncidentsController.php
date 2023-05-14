<?php

namespace App\Http\Controllers;

use PDF;
use Auth;
use View;
use Input;
use JavaScript;
use Carbon\Carbon;
use App\Http\Requests;
use App\Models\Vehicle;
use App\Models\Incident;
use App\Models\User;
use App\Models\Media;
use App\Models\TemporaryImage;
use Illuminate\Http\Request;
use App\Models\IncidentHistory;
use App\Models\ColumnManagements;
use App\Http\Controllers\Controller;
use App\Repositories\IncidentsRepository;
use App\Custom\Facades\GridEncoder;
use App\Http\Requests\StoreIncidentHistoryRequest;
use App\Services\UserService;

class IncidentsController extends Controller
{
    public $title= 'Reported Incidents';

    public function __construct() 
    {
        View::share('title', $this->title);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $vehicleRegistrations = Vehicle::select('registration as id', 'registration as text')
            // ->whereIn('vehicle_region',config('config-variables.userAccessibleRegionsForQuery'))
            ->leftjoin('vehicle_divisions','vehicles.vehicle_division_id', '=', 'vehicle_divisions.id')
            ->leftjoin('vehicle_regions','vehicles.vehicle_region_id', '=', 'vehicle_regions.id')
            ->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray())
            ->get();
        $columnManagement = ColumnManagements::where('user_id',$user->id)
                            ->where('section','incidents')
                            ->select('data')
                            ->first();
        $usersList = User::all();                    
        $vehicleDriverdata = array();
        foreach ($usersList as $key => $value) {   
            if($value->email) {
                $customString = substr($value->first_name, 0, 1).' '.$value->last_name . ' (' .$value->email . ')';    
            } else {
                $customString = substr($value->first_name, 0, 1).' '.$value->last_name . ' (' .$value->username . ')';
            }
            array_push($vehicleDriverdata, ['id'=>$value->id, 'text'=>$customString]);
        }

        $incidentAllocatedTo = [
            ['value'=>'Company', 'text'=>'Company'],
            ['value'=>'Insurance company', 'text'=>'Insurance company'],
            ['value'=>'Insurance broker', 'text'=>'Insurance broker']
        ];
        $vehicleListing = (new UserService())->getAllVehicleDashboardData();

        $incidentType = config('config-variables.incident_types');
        $incidentClassification = config('config-variables.incident_classification');

        JavaScript::put([
            'vehicleRegistrations' => $vehicleRegistrations,
            'columnManagement' => $columnManagement,
            'incidentAllocatedTo' => $incidentAllocatedTo,
            'vehicleDriverdata' => $vehicleDriverdata,
            'incidentClassification' => $incidentClassification
        ]);
        return view('incidents.index', compact('vehicleListing', 'incidentType'));
    }

    public function anyData()
    {
        return GridEncoder::encodeRequestedData(new IncidentsRepository(), Input::all());
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $incident = Incident::with('vehicle') ->whereHas('vehicle', function($q)
                {
                    // $q->whereIn('vehicle_region', config('config-variables.userAccessibleRegionsForQuery'));
                    $q->whereIn('vehicles.vehicle_region_id', Auth::user()->regions->lists('id')->toArray());
                })->find($id);
        if($incident == null){
             return redirect('/incidents');
        }
        $incidentStatus = [
            ['value'=>'Reported', 'text'=>'Reported'],
            ['value'=>'Under investigation', 'text'=>'Under investigation'],
            ['value'=>'Allocated', 'text'=>'Allocated'],
            ['value'=>'Closed', 'text'=>'Closed']
        ];

        $incidentInformed = [
            ['value'=>'Yes', 'text'=>'Yes'],
            ['value'=>'No', 'text'=>'No']
        ];

        $incidentAllocatedTo = [
            ['value'=>'Company', 'text'=>'Company'],
            ['value'=>'Insurance company', 'text'=>'Insurance company'],
            ['value'=>'Insurance broker', 'text'=>'Insurance broker']
        ];

        $incidentClassification = [
            'Glass damage' => [
                ['value'=>'Window screen', 'text'=>'Window screen'],
                ['value'=>'Front right', 'text'=>'Front right'],
                ['value'=>'Front left', 'text'=>'Front left'],
                ['value'=>'Back right', 'text'=>'Back right'],
                ['value'=>'Back left', 'text'=>'Back left'],
                ['value'=>'Other', 'text'=>'Other'],
            ],
            'Pedestrian incident' => [
                ['value'=>'Head-on collision', 'text'=>'Head-on collision'],
                ['value'=>'Reversing collision', 'text'=>'Reversing collision'],
                ['value'=>'Sideswipe collision', 'text'=>'Sideswipe collision'],
                ['value'=>'Other', 'text'=>'Other'],
            ],
            'Stolen vehicle' => [
                ['value'=>'Stolen', 'text'=>'Stolen'],
                ['value'=>'Other', 'text'=>'Other'],
            ],
            'Traffic incident' => [
                ['value'=>'Animal collision', 'text'=>'Animal collision'],
                ['value'=>'Bicycle collision', 'text'=>'Bicycle collision'],
                ['value'=>'Car collision', 'text'=>'Car collision'],
                ['value'=>'Motorbike collision', 'text'=>'Motorbike collision'],
                ['value'=>'Road debris collision', 'text'=>'Road debris collision'],
                ['value'=>'Stationary object', 'text'=>'Stationary object'],
                ['value'=>'Other', 'text'=>'Other'],
            ],
        ];

        $incidentType = [
            ['value'=>'Glass damage', 'text'=>'Glass damage'],
            ['value'=>'Pedestrian incident', 'text'=>'Pedestrian incident'],
            ['value'=>'Stolen vehicle', 'text'=>'Stolen vehicle'],
            ['value'=>'Traffic incident', 'text'=>'Traffic incident'],
        ];

        $images = $incident->getMedia();
        $comments = IncidentHistory::with('creator', 'updater')->where('incident_id', $id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();

        $vehicleDisplay = request()->get('vehicleDisplay');

        JavaScript::put([
            'incidentStatus' => $incidentStatus,
            'incidentInformed' => $incidentInformed,
            'incidentAllocatedTo' => $incidentAllocatedTo,
            'incidentClassification' => $incidentClassification,
            'incidentType' => $incidentType
        ]);

        return view('incidents.show', compact('incident', 'images', 'comments','vehicleDisplay'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $incident = Incident::with('vehicle')->findOrFail($id);
        $incidentStatus = [
            ['value'=>'Reported', 'text'=>'Reported'],
            ['value'=>'Under investigation', 'text'=>'Under investigation'],
            ['value'=>'Allocated', 'text'=>'Allocated'],
            ['value'=>'Closed', 'text'=>'Closed']
        ];

        $incidentInformed = [
            ['value'=>'1', 'text'=>'Yes'],
            ['value'=>'0', 'text'=>'No']
        ];

        $incidentAllocatedTo = [
            ['value'=>'Company', 'text'=>'Company'],
            ['value'=>'Insurance company', 'text'=>'Insurance company'],
            ['value'=>'Insurance broker', 'text'=>'Insurance broker']
        ];

        $incidentClassification = [
            'Glass damage' => [
                ['value'=>'Window screen', 'text'=>'Window screen'],
                ['value'=>'Front right', 'text'=>'Front right'],
                ['value'=>'Front left', 'text'=>'Front left'],
                ['value'=>'Back right', 'text'=>'Back right'],
                ['value'=>'Back left', 'text'=>'Back left'],
                ['value'=>'Other', 'text'=>'Other'],
            ],
            'Pedestrian incident' => [
                ['value'=>'Head-on collision', 'text'=>'Head-on collision'],
                ['value'=>'Reversing collision', 'text'=>'Reversing collision'],
                ['value'=>'Sideswipe collision', 'text'=>'Sideswipe collision'],
                ['value'=>'Other', 'text'=>'Other'],
            ],
            'Stolen vehicle' => [
                ['value'=>'Stolen', 'text'=>'Stolen'],
                ['value'=>'Other', 'text'=>'Other'],
            ],
            'Traffic incident' => [
                ['value'=>'Animal collision', 'text'=>'Animal collision'],
                ['value'=>'Bicycle collision', 'text'=>'Bicycle collision'],
                ['value'=>'Car collision', 'text'=>'Car collision'],
                ['value'=>'Motorbike collision', 'text'=>'Motorbike collision'],
                ['value'=>'Road debris collision', 'text'=>'Road debris collision'],
                ['value'=>'Stationary object', 'text'=>'Stationary object'],
                ['value'=>'Other', 'text'=>'Other'],
            ],
        ];

        $incidentType = [
            ['value'=>'Glass damage', 'text'=>'Glass damage'],
            ['value'=>'Pedestrian incident', 'text'=>'Pedestrian incident'],
            ['value'=>'Stolen vehicle', 'text'=>'Stolen vehicle'],
            ['value'=>'Traffic incident', 'text'=>'Traffic incident'],
        ];        

        $images = $incident->getMedia();
        $comments = IncidentHistory::with('creator', 'updater')->where('incident_id', $id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();

        JavaScript::put([
            'incident' => ['edit' => 'enabled'],
            'incidentStatus' => $incidentStatus,
            'incidentInformed' => $incidentInformed,
            'incidentAllocatedTo' => $incidentAllocatedTo,
            'incidentClassification' => $incidentClassification,
            'incidentType' => $incidentType
        ]);

        return view('incidents.show', compact('incident', 'images', 'comments'));
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

    public function storeComment(StoreIncidentHistoryRequest $request)
    {
        $incidentHistory = new IncidentHistory();
        $incidentHistory->incident_id = $request->incident_id;
        $incidentHistory->comments = $request->comments;
        $incidentHistory->created_by = Auth::id();
        $incidentHistory->updated_by = Auth::id();
        
        if($incidentHistory->save()){
            if (!empty($request->file())) {
                $fileName = $request->file('attachment')->getClientOriginalName();
                $customFileName = preg_replace('/\s+/', '_', $fileName);                
                if(!empty($request->file_input_name)) {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    $customFileName = $request->file_input_name . "." . $ext;
                }          
                $incidentHistoryMedia = IncidentHistory::findOrFail($incidentHistory->id);
                $fileToSave= $request->file('attachment')->getRealPath();
                $incidentHistoryMedia->addMedia($fileToSave)
                                    ->setFileName($customFileName)
                                    ->withCustomProperties(['mime-type' => $request->file('attachment')->getMimeType()])
                                    ->toCollectionOnDisk('incident_history', 'S3_uploads');
            }
            
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }

        return redirect('incidents/'.$request->incident_id);        
    }

    public function getIncidentComments($id)
    {
        $comments = IncidentHistory::with('creator', 'updater')->where('incident_id', $id)->orderBy('created_at', 'desc')->get();

        $incidentCommentsHtml = view('incidents.incident_comments', ['comments' => $comments])->render();
        return array('incidentCommentsHtml' => $incidentCommentsHtml);
    }

    public function updateComment(Request $request) 
    {
        $id = Input::get('pk');
        $value = Input::get('value');
        $field = Input::get('name');

        $comment = IncidentHistory::find($id);
        $comment->comments = $value;
        $comment->updated_by = Auth::id();
        $comment->save();
    }    

    public function downloadMedia($id)
    {
        $incident = IncidentHistory::findOrFail($id);        
        $media = $incident->getMedia();

        return redirect(getPresignedUrl($media[0]));
    }

    public function exportPdf($id)
    {
        $incident = Incident::with('vehicle')->findOrFail($id);
        $images = $incident->getMedia();
        $comments = IncidentHistory::with('creator', 'updater')->where('incident_id', $id)->orderBy('created_at', 'desc')->get();

        $tz = new \DateTimeZone('Europe/London');
        $date = new \DateTime(date('H:i:s d M Y'));
        $date->setTimezone($tz);
       
        $pdf = PDF::loadView('pdf.incidentHistoryExport', array('incident' => $incident, 'comments' => $comments, 'images' => $images))
            ->setPaper('a4')
            ->setOption('header-spacing', '5')
            ->setOption('header-font-size', 7)
            ->setOption('header-font-name', 'Open Sans')
            ->setOrientation('portrait')
            ->setOption('margin-top', 20)
            ->setOption('margin-bottom', 20);
        $pdf->setOption('header-html', view('pdf.header', compact('date')));

        $filename = $incident->vehicle->registration . '_' . 'incidenthistory' . $id . '_' . $incident->created_at->format('dmY') . '.pdf';
        return $pdf->download($filename);
    }

    public function updateDetails(Request $request) {
        $id = Input::get('pk');
        $value = Input::get('value');
        $field = Input::get('name');
        $commentValue = Input::get('commentValue');
        if ($field == 'incident_status') {
            $incident = Incident::find($id);
            $incident->status = $value;
            $incident->updated_by = Auth::id();
            if($incident->save()){
                $incidentHistory = new IncidentHistory();
                $incidentHistory->incident_id = $incident->id;
                $incidentHistory->type = "system";
                $incidentHistory->comments = 'set incident status to "'.$value.'" and added comment';
                $incidentHistory->incident_status_comment = $commentValue;
                $incidentHistory->created_by = Auth::id();
                $incidentHistory->updated_by = Auth::id();
                $incidentHistory->save();
            }
        }


        if ($field == 'incident_informed') {
            $incident = Incident::find($id);
            $incident->is_reported_to_insurance = $value;
           
            $incident->updated_by = Auth::id();
            if($incident->save()){
                $incidentHistory = new IncidentHistory();
                $incidentHistory->incident_id = $incident->id;
                $incidentHistory->type = "system";
                $incidentHistory->comments = 'updated insurance informed to "' .$value. '"';
                $incidentHistory->incident_status_comment = $commentValue;
                $incidentHistory->created_by = Auth::id();
                $incidentHistory->updated_by = Auth::id();
                $incidentHistory->save();
            }
        }

        if ($field == 'incident_allocated_to') {
            $incident = Incident::find($id);
            $incident->allocated_to = $value;
            $incident->updated_by = Auth::id();
            if($incident->save()){
                $incidentHistory = new IncidentHistory();
                $incidentHistory->incident_id = $incident->id;
                $incidentHistory->type = "system";
                $incidentHistory->comments = 'allocated incident to "' .$value. '"';
                $incidentHistory->incident_status_comment = $commentValue;
                $incidentHistory->created_by = Auth::id();
                $incidentHistory->updated_by = Auth::id();
                $incidentHistory->save();
            }
        }

        if ($field == 'incident_classification') {
            $incident = Incident::find($id);
            $incident->classification = $value ? $value : null;
            $incident->updated_by = Auth::id();
            if($incident->incident_type != Input::get('incidentType'))
            {
                $incident->incident_type = Input::get('incidentType');
                $incidentHistory = new IncidentHistory();
                $incidentHistory->incident_id = $incident->id;
                $incidentHistory->type = "system";
                $incidentHistory->comments = 'updated the incident type to "' .$incident->incident_type. '"';
                $incidentHistory->incident_status_comment = $commentValue;
                $incidentHistory->created_by = Auth::id();
                $incidentHistory->updated_by = Auth::id();
                $incidentHistory->save();
            }
            if($incident->save()){
                $incidentHistory = new IncidentHistory();
                $incidentHistory->incident_id = $incident->id;
                $incidentHistory->type = "system";
                $incidentHistory->comments = 'updated the classification to "' .$value. '"';
                $incidentHistory->incident_status_comment = $commentValue;
                $incidentHistory->created_by = Auth::id();
                $incidentHistory->updated_by = Auth::id();
                $incidentHistory->save();
                
            }
        }

        if ($field == 'incident_type') {
            $incident = Incident::find($id);
            $incident->incident_type = $value;
            $incident->updated_by = Auth::id();
            // if($incident->save()){
                // $incidentHistory = new IncidentHistory();
                // $incidentHistory->incident_id = $incident->id;
                // $incidentHistory->type = "system";
                // $incidentHistory->comments = 'updated the incident type to "'.$value. '"';
                // $incidentHistory->incident_status_comment = $commentValue;
                // $incidentHistory->created_by = Auth::id();
                // $incidentHistory->updated_by = Auth::id();
                // $incidentHistory->save();
            // }
        }

        if ($field == 'incident_date') {
            $incident = Incident::find($id);
            $incidentTime = Carbon::parse($incident->incident_date_time)->format('H:i:s');
            $incident->incident_date_time = $value . ' '. $incidentTime;
            $incident->updated_by = Auth::id();
            $value = Carbon::parse($value)->format('d M Y');
            if($incident->save()){
                $incidentHistory = new IncidentHistory();
                $incidentHistory->incident_id = $incident->id;
                $incidentHistory->type = "system";
                $incidentHistory->comments = 'updated Incident date to '.$value;
                $incidentHistory->incident_status_comment = $commentValue;
                $incidentHistory->created_by = Auth::id();
                $incidentHistory->updated_by = Auth::id();
                $incidentHistory->save();
            }
        }

        if ($field == 'incident_time') {
            $incident = Incident::find($id);
            $incidentDate = Carbon::parse($incident->incident_date_time)->format('Y-m-d');
            $incident->incident_date_time = $incidentDate . ' '. $value;
            $incident->updated_by = Auth::id();
            if($incident->save()){
                $incidentHistory = new IncidentHistory();
                $incidentHistory->incident_id = $incident->id;
                $incidentHistory->type = "system";
                $incidentHistory->comments = 'updated Incident time to '.$value;
                $incidentHistory->incident_status_comment = $commentValue;
                $incidentHistory->created_by = Auth::id();
                $incidentHistory->updated_by = Auth::id();
                $incidentHistory->save();
            }
        }
    }

    public function destroyComment($id)
    {
        if(IncidentHistory::where('id', $id)->delete()) {
            flash()->success(config('config-variables.flashMessages.dataDeleted'));
        }else{
            flash()->error(config('config-variables.flashMessages.dataNotDeleted'));
        }
        return redirect()->back();
    }

    public function createIncidentReport(Request $request)
    {
        $incident = new Incident();
        $vehicle = Vehicle::where('registration', $request['vehicleId'])->first();
        $incident->vehicle_id = $vehicle->id;
        $incident->incident_date_time = Carbon::createFromFormat('H:i:s d M Y',  $request['incident_datetime'])->format('Y-m-d H:i:s');
        $incident->incident_type = $request['incident_type'];
        $incident->classification = $request['classification'];
        $incident->is_reported_to_insurance = $request['is_reported_to_insurance'];
        $incident->created_by = Auth::id();
        $incident->updated_by = Auth::id();

        if ($incident->save()){
            $incidentHistory = new IncidentHistory();
            $incidentHistory->incident_id = $incident->id;
            $incidentHistory->type = "system";
            $incidentHistory->comments = 'created incident with incident type "' . $incident->incident_type . '" and classification "' . $incident->classification .'"';
            $incidentHistory->incident_status_comment = null;
            $incidentHistory->created_by = Auth::id();
            $incidentHistory->updated_by = Auth::id();
            $incidentHistory->save();

            // save incident images
            $images = $request->incident_images;
            if(isset($images) && !empty($images)) {
                foreach ($images as $key => $tempImageId) {
                    $tempImage = TemporaryImage::where('temp_id', $tempImageId)->first();
                    if ($tempImage) {
                        $media = $tempImage->getMedia()->first();
                        if ($media) {
                            $media->model_id = $incident->id;
                            $media->model_type = Incident::class;
                            $media->save();
                        }
                    }
                }
            }
        }

        return 'true';
    }

    public function uploadIncidentImages(Request $request)
    {
        $fileData = [];

        if($request->isMethod('post')) {
            $files = $request->file()['incident_images'];
            if (!empty($files)) {
                $i = 0;
                $caption = $request->name;
                $user = Auth::user();
                foreach ($files as $key => $value) {
                    $tempImage = new TemporaryImage();
                    $tempImage->model_id = 0;
                    $tempImage->model_type = Incident::class;
                    $tempImage->temp_id = time();
                    $tempId = $tempImage->temp_id;
                    $tempImage->save();

                    $fileName = $value->getClientOriginalName();
                    $customFileName = preg_replace('/\s+/', '_', $fileName);
                    $fileMime = $value->getMimeType();
                    $lastInsertedMedia = $tempImage->addMedia($value)
                        ->setFileName($customFileName)
                        ->withCustomProperties(['mime-type' => $value->getMimeType(), 'caption' => $caption, 'createdBy' => $user->id])
                        ->toCollectionOnDisk('incident', 'S3_uploads');

                    if ($lastInsertedMedia->hasCustomProperty('caption') && !empty($lastInsertedMedia->custom_properties['caption'])) {
                        $fileData['files'][$i]['name'] = $lastInsertedMedia->custom_properties['caption'] .".". pathinfo($fileName, PATHINFO_EXTENSION);
                    }
                    else {
                        $fileData['files'][$i]['name'] = $fileName;
                    }

                    $fileData['files'][$i]['created'] = $lastInsertedMedia->created_at->format('H:i:s d M Y');
                    $fileData['files'][$i]['size'] = $lastInsertedMedia->getHumanReadableSizeAttribute();
                    $fileData['files'][$i]['type'] = $fileMime;
                    $fileData['files'][$i]['url'] = getPresignedUrl($lastInsertedMedia);
                    $fileData['files'][$i]['deleteUrl'] = url('/incidents/delete_incident_image/' . $lastInsertedMedia->id);
                    $fileData['files'][$i]['deleteType'] = 'DELETE';
                    $fileData['files'][$i]['tempId'] = $tempId;
                    $i++;
                }
            }
        }
        return json_encode($fileData);
    }

    public function deleteIncidentImage($id)
    {
        $media = Media::find($id);
        if ($media->delete()) {
            return 1;
        } else {
            return 0;
        }
    }
}
