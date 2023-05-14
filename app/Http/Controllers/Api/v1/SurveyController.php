<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;

use App\Models\Survey;

class SurveyController extends APIController
{
    /**
     * Return the all the question of the survey master
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function questionSet(Request $request)
    {
        if((!empty($request->all())) && $request->has('registration_no')) {
            $vehicle = Vehicle::where('registration', $request->get('registration_no'))->first();
            if ($vehicle) {
                $surveyQuestions = Survey::where(\DB::raw("FIND_IN_SET('" . $vehicle->type->id . "', vehicle_type)"), '<>' ,0)->get()->toArray();
                return $this->response->array($surveyQuestions);
            }
        }
        else {
            $surveyQuestions = Survey::all()->toArray();
            return $this->response->array($surveyQuestions);
        }
    }

    /**
     * Return the question json for the survey master id
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function screenJson(Request $request)
    {
        $survey = Survey::select('screen_json')->findOrFail($request->get('id'));
        return $this->response->array($survey->screen_json);
    }
}
