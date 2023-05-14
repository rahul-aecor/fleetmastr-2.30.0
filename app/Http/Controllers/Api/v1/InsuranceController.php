<?php

namespace App\Http\Controllers\Api\v1;

use Auth;
use App\Models\User;
use App\Models\Media;
use App\Http\Requests;
use App\Models\Settings;
use App\Models\Incident;
use App\Models\IncidentHistory;
use Illuminate\Http\Request;
use App\Models\TemporaryImage;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use JWTAuth;

class InsuranceController extends APIController
{
	public function getInsuranceDetail()
	{
        $insuranceDetail = Settings::where('key','accident_insurance_detail')->first();
        $insuranceDetail = json_decode($insuranceDetail->value, true);

        if (isset($insuranceDetail['insurance_certificate_attachment']) && $insuranceDetail['insurance_certificate_attachment'] != "") {
            $insuranceDetail['insurance_certificate_attachment'] = getPreSingedUrlFromNormalUrl($insuranceDetail['insurance_certificate_attachment']);
        }
        return $insuranceDetail;
	}

	public function saveIncidentDetail(Request $request) 
	{
		$user = JWTAuth::parseToken()->toUser();
		$incident = new Incident();
		$incident->vehicle_id = $request['vehicle_id'];
		$incident->incident_date_time = Carbon::parse($request['incident_date_time'])->format('Y-m-d H:i:s');
		$incident->incident_type = $request['incident_type'];
		$incident->classification = $request['classification'];
		$incident->is_reported_to_insurance = $request['is_reported_to_insurance'];
		$incident->created_by = $user->id;
        $incident->updated_by = $user->id;

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
			$this->addImageTempIds($incident, $request->all());
		}

		return response()->json(['message' => "Incident has been saved successfully", "status_code" => 200], 200);
	}

	public function addImageTempIds($incident, $data)
	{
		if(!empty($data['image_string'])) {
			$tempIds = explode("|", $data['image_string']);
			foreach ($tempIds as $tempId) {
				$tempImage = new TemporaryImage();
	            $tempImage->model_id = $incident->id;
	            $tempImage->model_type = Incident::class;
	            $tempImage->temp_id = $tempId;
	            $tempImage->save();
			}
		}
		
	}
}