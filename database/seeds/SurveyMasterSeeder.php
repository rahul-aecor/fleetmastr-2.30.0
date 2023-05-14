<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Survey;

class SurveyMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('survey_master')->truncate();

        $surveys = DB::table('defect_master_vehicle_types')
            ->join('vehicle_types', 'vehicle_types.id', '=', 'defect_master_vehicle_types.vehicle_type_id')
            ->select('vehicle_types.vehicle_category', DB::raw('GROUP_CONCAT(defect_master_vehicle_types.vehicle_type_id) as vehicle_types'),'defect_master_vehicle_types.defect_list', 'vehicle_types.engine_type')
            ->groupBy('vehicle_types.vehicle_category')
            ->groupBy('vehicle_types.engine_type')
            ->groupBy('defect_master_vehicle_types.defect_list')
            ->get();

        foreach ($surveys as $survey) {

            $AdBlueRequired = 0;
            $skipDefects = env('SKIP_DEFECT') != '' ? explode(',', env('SKIP_DEFECT')) : '';
            $removeDefects = env('REMOVE_DEFECT') != '' ? explode(',', env('REMOVE_DEFECT')) : '';
            //if($survey->engine_type == "Post-Euro VI - AdBlue required"){
            //if($survey->engine_type == "Diesel Electric"){
            if(strtolower($survey->engine_type) == strtolower("Euro VI Diesel (Adblue)")){
                $AdBlueRequired = 1;
            }

            //Checkout Json
            $newSurvey = new Survey();
            $newSurvey->vehicle_category = $survey->vehicle_category;
            $newSurvey->action = 'checkout';
            $newSurvey->vehicle_type = $survey->vehicle_types;
            $newSurvey->desc = $survey->vehicle_category . " Vehicle checkout Screen";
            $newSurvey->screen_json = $this->getCheckoutJson($survey->vehicle_category, $survey->defect_list, $skipDefects, $removeDefects, $AdBlueRequired);
            $newSurvey->save();

            //on-call Json
            $newSurvey = new Survey();
            $newSurvey->vehicle_category = $survey->vehicle_category;
            $newSurvey->action = 'on-call';
            $newSurvey->vehicle_type = $survey->vehicle_types;
            $newSurvey->desc = $survey->vehicle_category . " On-Call Vehicle checkout Screen";
            $newSurvey->screen_json = $this->getCheckoutOnCallJson($survey->vehicle_category, $survey->defect_list, $skipDefects, $removeDefects, $AdBlueRequired);
            $newSurvey->save();

            //Checkin Json
            $newSurvey = new Survey();
            $newSurvey->vehicle_category = $survey->vehicle_category;
            $newSurvey->action = 'checkin';
            $newSurvey->vehicle_type = $survey->vehicle_types;
            $newSurvey->desc = $survey->vehicle_category . " Vehicle checkin Screen";
            $newSurvey->screen_json = $this->getCheckinJson($survey->vehicle_category, $survey->defect_list, $skipDefects, $removeDefects, $AdBlueRequired);
            $newSurvey->save();

            //Report a Defect Json
            $newSurvey = new Survey();
            $newSurvey->vehicle_category = $survey->vehicle_category;
            $newSurvey->action = 'defect';
            $newSurvey->vehicle_type = $survey->vehicle_types;
            $newSurvey->desc = $survey->vehicle_category . " Vehicle report a defect Screen";
            $newSurvey->screen_json = $this->getDefectJson($survey->vehicle_category, $survey->defect_list, $skipDefects, $removeDefects, $AdBlueRequired);
            $newSurvey->save();

        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function getCheckoutJson($vehicleCategory, $orderList, $orderListSkip, $orderListRemove, $AdBlueRequired)
    {
        $screen_no = 1;
        $action_json1 = "";
        $multiselect_json = "";
        $dynamicDefects = json_decode(env('HAVING_DYNAMIC_DEFECTS'),true);
        $action_json = '
        {
            "_number": "'.$screen_no.'",
            "_type": "action",
            "regno": "",
            "title": "Start Your Vehicle Check",
            "text": "Start your vehicle and commence the walk-around checks over the following screens.",
            "buttons": {
                "show_save": "yes",
                "show_continue": "yes",
                "show_no": "no",
                "show_yes": "no"
            },
            "buttons_screen": {
                "on_continue": "'.($screen_no+1).'",
                "on_save": "'.$screen_no++.'",
                "on_no": "",
                "on_yes": ""
            },
            "defects": {},
            "options": {
                "optionList": []
            }
        }';

        $trailerAttachedJson = "";
        if(setting('is_trailer_feature_enabled')) {
            $trailerAttachedScreen = $screen_no;
            $trailerAttachedJson = '
            {
                "_number": "'.$screen_no.'",
                "_type": "confirm_with_input",
                "regno": "",
                "title": "Trailer Check",
                "text": "Is there a trailer attached to the vehicle?",
                "show_input_on": "yes",
                "input_question_text": "Enter trailer ID",
                "input_answer": "",
                "is_read_only": "yes",
                "buttons": {
                    "show_save": "yes",
                    "show_continue": "yes",
                    "show_no": "yes",
                    "show_yes": "yes"
                },
                "button_options": {
                    "show_no": "yes",
                    "show_yes": "yes"
                },
                "buttons_screen": {
                    "on_continue": "'.($screen_no+1).'",
                    "on_save": "'.$screen_no++.'",
                    "on_no": "'. $screen_no .'",
                    "on_yes": "'. $screen_no .'"
                },
                "defects": {},
                "options": {
                    "optionList": []
                }
            }';
        }

        $yesno_query = \DB::table('defect_master')
            ->select('order',\DB::raw("GROUP_CONCAT(TRIM(defect_order) ORDER BY defect_order DESC, defect, id SEPARATOR ',') as defect_order"), 'page_title','id', 'type', 'app_question', 'app_question_with_defect', 'show_warning'    , 'warning_text',
                \DB::raw("COUNT(id) as cnt"),
                \DB::raw("is_prohibitional as prohibitional"),
                \DB::raw("safety_notes as safetynotes"),
                \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, defect, id SEPARATOR '|' ) as ids"),
                \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, defect, id SEPARATOR '|') as defect"),
                \DB::raw("GROUP_CONCAT( has_not_applicable_option ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_not_applicable_option"),
                \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_image"),
                \DB::raw("has_image as is_has_image"),
                \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_text"),
                \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, defect, id SEPARATOR '|') as is_prohibitional"),
                \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, defect, id SEPARATOR '|') as safety_notes")
            );

        $yesno_query = $yesno_query->where('order','>=',0);
        $yesno_query = $yesno_query->whereNotIn('order', explode(',', env('TRAILER_QUESTIONS_ORDER')));
        $yesno_query = $yesno_query->whereIn('order', explode(',', $orderList))
            ->where('for_'.$vehicleCategory,'=',1)
            ->groupBy('order');

        if(!empty($orderListSkip)){
            $yesno_query->whereNotIn('order', $orderListSkip);
        }

        $yesno_query_data = $yesno_query->get();

        $yesno_json = "";
        foreach ($yesno_query_data as $row) {

            if (isset($dynamicDefects[$row->order])) {
                $isDynamicDefect = 1;
            } else {
                $isDynamicDefect = 0;
                $dynamicDefects[$row->order] = [];
            }
            $idsList = explode("|", $row->ids);
            $defectList = explode("|", $row->defect);
            $defectOrderList = explode(",", $row->defect_order);
            $dynamicDefectId = "";
            foreach ($defectList as $key => $value) {
                if (in_array($defectOrderList[$key], $dynamicDefects[$row->order])) {
                    $dynamicDefectId = $idsList[$key];
                    break;
                }
            }
            if($row->type == 'media_based_on_selection') {
                $yesno_json .= '
                    {
                        "_number": "' . $screen_no . '",
                        "_type": "media_based_on_selection",';
            } else {
                $yesno_json .= '
                    {
                        "_number": "' . $screen_no . '",
                        "_type": "yesno",';
            }

            $yesno_json .= '
                    "dependent_screen": "",
                    "dependent_answer": "",
                    "regno": "",
                    "title": "' . $row->page_title . '",';
            if ($row->order == 0 && $AdBlueRequired) {
                $yesno_json .= '"text": "Are the vehicle\'s AdBlue, coolant, engine oil and screenwash levels sufficient?",';
            } else {
                $yesno_json .= '"text": "' . $row->app_question . '",';
            }
            if ($row->order == 0 && $AdBlueRequired) {
                $yesno_json .= '"pre_text": "Are the vehicle\'s AdBlue, coolant, engine oil and screenwash levels sufficient?",';
            } else {
                $yesno_json .= '"pre_text": "' . $row->app_question_with_defect . '",';
            }

            if ($row->show_warning == 1) {
                $yesno_json .= '"show_warning": "yes",
                                "warning_text": "' . $row->warning_text . '",';
            }

            $has_not_applicable_options = explode("|", $row->has_not_applicable_option);
            $yesno_json .= '"buttons": {
                        "show_save": "yes",
                        "show_continue": "yes"
                    },
                    "button_options": {
                        "show_no": "yes",
                        "show_yes": "yes",
                        "show_not_applicable": ' . ($has_not_applicable_options[0] == 1 ? '"yes"' : '"no"') . '
                    },
                    "buttons_screen": {
                        "on_continue": "",
                        "on_no": "showDefectDialog",
                        "on_yes": "' . ($screen_no + 1) . '",
                        "on_save": "' . ($screen_no++) . '"
                    },
                    "defects": {
                        "title": "' . $row->page_title . '",
                        "is_having_dynamic_defects" : "' . $isDynamicDefect . '", 
                        "is_dynamic_defects_having_image" : "' . $row->is_has_image . '",
                        "is_dynamic_defect_prohibitional" : "'.$row->prohibitional.'", 
                        "dynamic_defect_safety_notes" : "'.$row->safetynotes.'",
                        "dynamic_defect_id" : "' . $dynamicDefectId . '", 
                        "defect_id" : "' . $row->id . '", 
                        "buttons": {
                            "show_cancel": "yes",
                            "show_continue": "yes",
                            "show_no": "no",
                            "show_save": "no",
                            "show_yes": "no"
                        },
                        "defect": [
                    ';

            //if ($isDynamicDefect == 0) {
            $defectList = explode("|", $row->defect);
            $defectOrderList = explode(",", $row->defect_order);
            $has_imageList = explode("|", $row->has_image);
            $has_textList = explode("|", $row->has_text);
            $is_prohibitionalList = explode("|", $row->is_prohibitional);
            $safety_notesList = explode("|", $row->safety_notes);
            $idsList = explode("|", $row->ids);
            foreach ($defectList as $key => $value) {
                if (strpos(strtolower($value), 'adblue') !== false && $AdBlueRequired === 0 && $row->order == 0) {
                    continue;
                }

                if (in_array($defectOrderList[$key], $dynamicDefects[$row->order]) == false) {

                    $yesno_json .= '{
                                "id": "' . $idsList[$key] . '",
                                "_image": "' . (($has_imageList[$key] == 0) ? "no" : "yes") . '",
                                "_text": "' . (($has_textList[$key] == 0) ? "no" : "yes") . '",
                                "imageString": "",
                                "image_exif": "",
                                "selected": "no",
                                "text": "' . $defectList[$key] . '",
                                "textString": "",
                                "prohibitional": "' . (($is_prohibitionalList[$key] == 0) ? "no" : "yes") . '",
                                "safety_notes": "' . $safety_notesList[$key] . '"
                            }' . (($key == ($row->cnt - 1)) ? "" : ",");
                }

            }
            $yesno_json .= ']
                        },
                        "options": {
                            "optionList": [';

            if ($row->order == 0) {
                $defectList = explode("|", $row->defect);
                $idsList = explode("|", $row->ids);
                foreach ($defectList as $key => $value) {
                    if (strpos(strtolower($value), 'adblue') !== false) {
                        if ($AdBlueRequired) {
                            $yesno_json .= '{
                                "id": "' . $idsList[$key] . '",
                                "_mandatory": "yes",
                                "text": "' . $defectList[$key] . '",
                                "answer": ""
                                },';
                        }
                    } else {
                        $yesno_json .= '{
                            "id": "' . $idsList[$key] . '",
                            "_mandatory": "yes",
                            "text": "' . $defectList[$key] . '",
                            "answer": ""
                            },';
                    }
                }
                $yesno_json = rtrim($yesno_json, ',');
            }

            $yesno_json .= ']}';

            if($row->type == 'media_based_on_selection') {
                $yesno_json .= ',"media_dependent_on_answer": "yes",';
                if(strpos($row->defect, 'max_media_') !== false) {
                    $yesno_json .= '"maximum_allowed_image": '.str_replace('max_media_', '', $row->defect).',';
                    $yesno_json .= '"media_label_text": "",';
                    $yesno_json .= '"validation_hint_message": "",';
                } else {
                    $yesno_json .= '"maximum_allowed_image": 1,';
                    $yesno_json .= '"media_label_text": "'.$row->defect.'",';
                    $yesno_json .= '"validation_hint_message": "Need to capture 1 image",';
                }
                $yesno_json .= '"minimum_required_image": 1,';
                $yesno_json .= '"radio_options": '. '[
                    {
                        "key": "yes",
                        "value": "Yes"
                    },
                    {
                        "key": "no",
                        "value": "No"
                    }
                ]';
            }

            $yesno_json .= '},';

        }
        $yesno_json = rtrim($yesno_json, ',');

        $trailerQuestionsJson = "";
        if(setting('is_trailer_feature_enabled')) {
            $trailerQuestionsQuery = \DB::table('defect_master')
                ->select('order',\DB::raw("GROUP_CONCAT(TRIM(defect_order) ORDER BY defect_order DESC, defect, id SEPARATOR ',') as defect_order"), 'page_title', 'type', 'app_question','id', 'app_question_with_defect',
                    \DB::raw("COUNT(id) as cnt"),
                    \DB::raw("is_prohibitional as prohibitional"),
                    \DB::raw("safety_notes as safetynotes"),
                    \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, defect, id SEPARATOR '|' ) as ids"),
                    \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, defect, id SEPARATOR '|') as defect"),
                    \DB::raw("GROUP_CONCAT( has_not_applicable_option ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_not_applicable_option"),
                    \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_image"),
                    \DB::raw("has_image as is_has_image"),
                    \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_text"),
                    \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, defect, id SEPARATOR '|') as is_prohibitional"),
                    \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, defect, id SEPARATOR '|') as safety_notes")
                )
                ->whereIn('order', explode(",", env('TRAILER_QUESTIONS_ORDER')))
                ->groupBy('order')
                ->get();

            $trailerQuestionsJson = "";
            foreach ($trailerQuestionsQuery as $row) {

                if (isset($dynamicDefects[$row->order])) {
                    $isDynamicDefect = 1;
                } else {
                    $isDynamicDefect = 0;
                    $dynamicDefects[$row->order] = [];
                }
                $idsList = explode("|", $row->ids);
                $defectList = explode("|", $row->defect);
                $defectOrderList = explode(",", $row->defect_order);
                $dynamicDefectId = "";
                foreach ($defectList as $key => $value) {
                    if (in_array($defectOrderList[$key], $dynamicDefects[$row->order])) {
                        $dynamicDefectId = $idsList[$key];
                        break;
                    }
                }

                $trailerQuestionsJson .= '
                    {
                        "_number": "'.$screen_no.'",
                        "_type": "' . $row->type . '",
                        "dependent_screen": "' . $trailerAttachedScreen . '",
                        "dependent_answer": "yes",
                        "related_to": "trailer",
                        "regno": "",
                        "title": "'.$row->page_title.'",';
                $trailerQuestionsJson .= '"text": "'.$row->app_question.'",';
                $trailerQuestionsJson .= '"pre_text": "'.$row->app_question_with_defect.'",';
                $has_not_applicable_options = explode("|", $row->has_not_applicable_option);
                $trailerQuestionsJson .= '"buttons": {
                            "show_save": "yes",
                            "show_continue": "yes"
                        },
                        "buttons_screen": {
                                "on_continue": "",
                                "on_no": "showDefectDialog",
                                "on_yes": "'.($screen_no + 1).'",
                                "on_save": "'.($screen_no++).'"
                            },';

                if($row->type == 'yesno') {
                    $trailerQuestionsJson .= '"button_options": {
                            "show_no": "yes",
                            "show_yes": "yes",
                            "show_not_applicable": ' . ($has_not_applicable_options[0] == 1 ? '"yes"' : '"no"') . '
                        },';
                }

                if($row->type == 'media_based_on_selection') {
                    $trailerQuestionsJson .= '"media_dependent_on_answer": "yes",';
                    $trailerQuestionsJson .= '"maximum_allowed_image": 1,';
                    $trailerQuestionsJson .= '"media_label_text": "'.$row->defect.'",';
                    $trailerQuestionsJson .= '"minimum_required_image": 1,';
                    $trailerQuestionsJson .= '"validation_hint_message": "Need to capture 1 image",';
                    $trailerQuestionsJson .= '"radio_options": '. '[
                        {
                            "key": "yes",
                            "value": "Yes"
                        },
                        {
                            "key": "no",
                            "value": "No"
                        }
                    ]';
                }

                if($row->type == 'media') {
                    $trailerQuestionsJson .= '"validation_hint_message": "Need to capture min of 2 and max of 3 images",';
                    $trailerQuestionsJson .= '"maximum_allowed_image": 3,';
                    $trailerQuestionsJson .= '"minimum_required_image": 2';
                }

                if($row->type == 'multiinput') {
                    $trailerQuestionsJson .= '"inputs": ' . $row->defect . ',';
                    $trailerQuestionsJson .= '"validation_hint_message": "Minimum of one to be completed",';
                    $trailerQuestionsJson .= '"minimum_required_input": 1';
                }

                if($row->type == 'dropdown') {
                    $trailerQuestionsJson .= '"dropdowns": ' . $row->defect . ',';
                    $trailerQuestionsJson .= '"validation_hint_message": "A minimum of one dropdown has to be completed",';
                    $trailerQuestionsJson .= '"minimum_dropdown_to_be_filled": 1';
                }

                if($row->type == 'yesno') {
                    $trailerQuestionsJson .= '
                            "defects": {
                                "title": "'.$row->page_title.'",
                                "is_having_dynamic_defects" : "'.$isDynamicDefect.'", 
                                "is_dynamic_defects_having_image" : "'.$row->is_has_image.'",
                                "is_dynamic_defect_prohibitional" : "'.$row->prohibitional.'",
                                "dynamic_defect_safety_notes" : "'.$row->safetynotes.'",
                                "dynamic_defect_id" : "' . $dynamicDefectId . '", 
                                "defect_id" : "'.$row->id.'",
                                "buttons": {
                                    "show_cancel": "yes",
                                    "show_continue": "yes",
                                    "show_no": "no",
                                    "show_save": "no",
                                    "show_yes": "no"
                                },
                                "defect": [
                            ';
                    $defectList = explode("|", $row->defect);
                    $defectOrderList = explode(",", $row->defect_order);
                    $has_imageList = explode("|", $row->has_image);
                    $has_textList = explode("|", $row->has_text);
                    $is_prohibitionalList = explode("|", $row->is_prohibitional);
                    $safety_notesList = explode("|", $row->safety_notes);
                    $idsList = explode("|", $row->ids);
                    foreach ($defectList as $key => $value) {

                        if (in_array($defectOrderList[$key],$dynamicDefects[$row->order]) == false) {
                            $trailerQuestionsJson .= '{
                                            "id": "' . $idsList[$key] . '",
                                            "_image": "' . (($has_imageList[$key] == 0) ? "no" : "yes") . '",
                                            "_text": "' . (($has_textList[$key] == 0) ? "no" : "yes") . '",
                                            "imageString": "",
                                            "image_exif": "",
                                            "selected": "no",
                                            "text": "' . $defectList[$key] . '",
                                            "textString": "",
                                            "prohibitional": "' . (($is_prohibitionalList[$key] == 0) ? "no" : "yes") . '",
                                            "safety_notes": "' . $safety_notesList[$key] . '"
                                        }' . (($key == ($row->cnt - 1)) ? "" : ",");
                        }

                    }
                    $trailerQuestionsJson .= ']
                                },
                                "options": {
                                    "optionList": []}';
                }
                $trailerQuestionsJson .= '},';
            }
            $trailerQuestionsJson = rtrim($trailerQuestionsJson, ',');
        }

        $final_json = '{
            "status": "RoadWorthy|SafeToOperate|UnsafeToOperate",
            "screens": {
                "screen": [' . $action_json1 . ($multiselect_json ? $multiselect_json . "," : "") . $action_json . "," . ($trailerAttachedJson ? $trailerAttachedJson . "," : "") . $yesno_json . ($trailerQuestionsJson ? "," . $trailerQuestionsJson : "") . ']}}';
        $final_json = str_replace('"on_yes": "'.$screen_no.'"', '"on_yes": "showReviewScreen"', $final_json);

        // \Log::info("hello");
        // \Log::info($final_json);exit;
        // exit;
        $jsonObj = json_decode($final_json);
        $minifiedJson = json_encode($jsonObj);
        

        return $minifiedJson;
    }

    private function getCheckoutOnCallJson($vehicleCategory, $orderList, $orderListSkip, $orderListRemove, $AdBlueRequired)
    {
        $screen_no = 1;
        $action_json1 = "";
        $multiselect_json = "";
        $dynamicDefects = json_decode(env('HAVING_DYNAMIC_DEFECTS'),true);
        $startYourVehicleScreen = '
        {
            "_number": "'.$screen_no.'",
            "_type": "action",
            "regno": "",
            "title": "Start Your Vehicle Check",
            "text": "Start your vehicle and commence the walk-around checks over the following screens.",
            "buttons": {
                "show_save": "yes",
                "show_continue": "yes",
                "show_no": "no",
                "show_yes": "no"
            },
            "buttons_screen": {
                "on_continue": "'.($screen_no+1).'",
                "on_save": "'.$screen_no++.'",
                "on_no": "",
                "on_yes": ""
            },
            "defects": {},
            "options": {
                "optionList": []
            }
        }';

        $trailerAttachedJson = "";
        if(setting('is_trailer_feature_enabled')) {
            $trailerAttachedScreen = $screen_no;
            $trailerAttachedJson = '
            {
                "_number": "'.$screen_no.'",
                "_type": "confirm_with_input",
                "regno": "",
                "title": "Trailer Check",
                "text": "Is there a trailer attached to the vehicle?",
                "show_input_on": "yes",
                "input_question_text": "Enter trailer ID",
                "input_answer": "",
                "is_read_only": "yes",
                "buttons": {
                    "show_save": "yes",
                    "show_continue": "yes",
                    "show_no": "yes",
                    "show_yes": "yes"
                },
                "button_options": {
                    "show_no": "yes",
                    "show_yes": "yes"
                },
                "buttons_screen": {
                    "on_continue": "'.($screen_no+1).'",
                    "on_save": "'.$screen_no++.'",
                    "on_no": "'. $screen_no .'",
                    "on_yes": "'. $screen_no .'"
                },
                "defects": {},
                "options": {
                    "optionList": []
                }
            }';
        }

        $list_query = \DB::table('defect_master')
            ->select('order',\DB::raw("GROUP_CONCAT(TRIM(defect_order) ORDER BY defect_order DESC, defect, id SEPARATOR ',') as defect_order"), 'page_title', 'type', 'app_question', 'id',
                \DB::raw("COUNT(id) as cnt"),
                \DB::raw("is_prohibitional as prohibitional"),
                \DB::raw("safety_notes as safetynotes"),
                \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, defect, id SEPARATOR '|' ) as ids"),
                \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, defect, id SEPARATOR '|') as defect"),
                \DB::raw("GROUP_CONCAT( has_not_applicable_option ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_not_applicable_option"),
                \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_image"),
                \DB::raw("has_image as is_has_image"),
                \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_text"),
                \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, defect, id SEPARATOR '|') as is_prohibitional"),
                \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, defect, id SEPARATOR '|') as safety_notes")
            );

        $list_query = $list_query->where('order','>=',0);
        $list_query = $list_query->whereNotIn('order', explode(',', env('TRAILER_QUESTIONS_ORDER')));
        $list_query = $list_query->whereIn('order', explode(',', $orderList))
            ->where('for_'.$vehicleCategory,'=',1)
            ->groupBy('order');
        if(!empty($orderListSkip)){
            $list_query->whereNotIn('order', $orderListSkip);
        }
        $list_query_data = $list_query->get();

        $list_json = '{
        "status": "RoadWorthy|SafeToOperate|UnsafeToOperate",
        "screens": {
            "screen": [' . $action_json1 . ($multiselect_json ? $multiselect_json . "," : "") . $startYourVehicleScreen . ($trailerAttachedJson ? "," . $trailerAttachedJson : "") .',
                {
                    "_number": "'.$screen_no.'",
                    "_type": "list",
                    "regno": "",
                    "title": "Safety Check",
                    "text": "Tap an item below to report a defect",
                    "buttons": {
                        "show_continue": "yes",
                        "show_save": "yes",
                        "show_no": "no",
                        "show_yes": "no"
                    },
                    "buttons_screen": {
                        "on_continue": "showReviewScreen",
                        "on_save": "'.$screen_no.'",
                        "on_no": "",
                        "on_yes": ""
                    },
                    "defects": {},
                    "options": {
                        "optionList": [
        ';

        foreach ($list_query_data as $row)
        {
            if (isset($dynamicDefects[$row->order])) {
                $isDynamicDefect = 1;
            } else {
                $isDynamicDefect = 0;
                $dynamicDefects[$row->order] = [];
            }
            $idsList = explode("|", $row->ids);
            $defectList = explode("|", $row->defect);
            $defectOrderList = explode(",", $row->defect_order);
            $dynamicDefectId = "";
            foreach ($defectList as $key => $value) {
                if (in_array($defectOrderList[$key], $dynamicDefects[$row->order])) {
                    $dynamicDefectId = $idsList[$key];
                    break;
                }
            }
            $list_json .= '{
                "_mandatory": "yes",
                "text": "'.$row->page_title.'",
                "defects": {
                    "title": "'.$row->page_title.'",
                    "is_having_dynamic_defects" : "'.$isDynamicDefect.'", 
                    "is_dynamic_defects_having_image" : "'.$row->is_has_image.'",
                    "is_dynamic_defect_prohibitional" : "'.$row->prohibitional.'",
                    "dynamic_defect_safety_notes" : "'.$row->safetynotes.'",
                    "dynamic_defect_id" : "' . $dynamicDefectId . '", 
                    "defect_id" : "'.$row->id.'", 
                    "buttons": {
                        "show_cancel": "yes",
                        "show_continue": "yes",
                        "show_no": "no",
                        "show_save": "no",
                        "show_yes": "no"
                    },
                    "defect": [';


            $defectList = explode("|", $row->defect);
            $defectOrderList = explode(",", $row->defect_order);
            $has_imageList = explode("|", $row->has_image);
            $has_textList = explode("|", $row->has_text);
            $is_prohibitionalList = explode("|", $row->is_prohibitional);
            $safety_notesList = explode("|", $row->safety_notes);
            $idsList = explode("|", $row->ids);
            foreach ($defectList as $key => $value) {
                if (strpos(strtolower($value), 'adblue') !== false && $AdBlueRequired === 0 && $row->order == 0) {
                    continue;
                }

                if (in_array($defectOrderList[$key],$dynamicDefects[$row->order]) == false) {
                    $list_json .= ' {
                                        "id": "' . $idsList[$key] . '",
                                        "_image": "' . (($has_imageList[$key] == 0) ? "no" : "yes") . '",
                                        "_text": "' . (($has_textList[$key] == 0) ? "no" : "yes") . '",
                                        "imageString": "",
                                        "image_exif": "",
                                        "selected": "no",
                                        "text": "' . $defectList[$key] . '",
                                        "textString": "",
                                        "prohibitional": "' . (($is_prohibitionalList[$key] == 0) ? "no" : "yes") . '",
                                        "safety_notes": "' . $safety_notesList[$key] . '"
                                    }' . (($key == ($row->cnt - 1)) ? "" : ",");
                }
            }
            $list_json .= ']
                }
            },';
        }

        $trailerQuestionsJson = "";
        if(setting('is_trailer_feature_enabled')) {
            $trailerQuestionsQuery = \DB::table('defect_master')
                ->select('order',\DB::raw("GROUP_CONCAT(TRIM(defect_order) ORDER BY defect_order DESC, defect, id SEPARATOR ',') as defect_order"), 'page_title', 'type', 'app_question','id',
                    \DB::raw("COUNT(id) as cnt"),
                    \DB::raw("is_prohibitional as prohibitional"),
                    \DB::raw("safety_notes as safetynotes"),
                    \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, defect, id SEPARATOR '|' ) as ids"),
                    \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, defect, id SEPARATOR '|') as defect"),
                    \DB::raw("GROUP_CONCAT( has_not_applicable_option ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_not_applicable_option"),
                    \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_image"),
                    \DB::raw("has_image as is_has_image"),
                    \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_text"),
                    \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, defect, id SEPARATOR '|') as is_prohibitional"),
                    \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, defect, id SEPARATOR '|') as safety_notes")
                )
                ->whereIn('order', explode(",", env('TRAILER_QUESTIONS_ORDER')))
                ->groupBy('order')
                ->get();

            foreach ($trailerQuestionsQuery as $row)
            {
                if (isset($dynamicDefects[$row->order])) {
                    $isDynamicDefect = 1;
                } else {
                    $isDynamicDefect = 0;
                    $dynamicDefects[$row->order] = [];
                }
                $idsList = explode("|", $row->ids);
                $defectList = explode("|", $row->defect);
                $defectOrderList = explode(",", $row->defect_order);
                $dynamicDefectId = "";
                foreach ($defectList as $key => $value) {
                    if (in_array($defectOrderList[$key], $dynamicDefects[$row->order])) {
                        $dynamicDefectId = $idsList[$key];
                        break;
                    }
                }
                $list_json .= '{
                        "_mandatory": "yes",
                        "text": "'.$row->page_title.'",
                        "dependent_screen": "' . $trailerAttachedScreen . '",
                        "dependent_answer": "yes",';
                if($row->type === 'yesno') {
                    $list_json .= '"related_to": "trailer",';
                } else {
                    $list_json .= '"related_to": "trailer"';
                }
                
                if($row->type === 'yesno') {
                    $list_json .= '"defects": {
                                "title": "'.$row->page_title.'",
                                "is_having_dynamic_defects" : "'.$isDynamicDefect.'", 
                                "is_dynamic_defects_having_image" : "'.$row->is_has_image.'",
                                "is_dynamic_defect_prohibitional" : "'.$row->prohibitional.'",
                                "dynamic_defect_safety_notes" : "'.$row->safetynotes.'",
                                "dynamic_defect_id" : "' . $dynamicDefectId . '",  
                                "defect_id" : "'.$row->id.'", 
                                "buttons": {
                                    "show_cancel": "yes",
                                    "show_continue": "yes",
                                    "show_no": "no",
                                    "show_save": "no",
                                    "show_yes": "no"
                                },
                                "defect": [';
                    $defectList = explode("|", $row->defect);
                    $defectOrderList = explode(",", $row->defect_order);
                    $has_imageList = explode("|", $row->has_image);
                    $has_textList = explode("|", $row->has_text);
                    $is_prohibitionalList = explode("|", $row->is_prohibitional);
                    $safety_notesList = explode("|", $row->safety_notes);
                    $idsList = explode("|", $row->ids);
                    foreach ($defectList as $key => $value) {

                        if (in_array($defectOrderList[$key],$dynamicDefects[$row->order]) == false) {
                            $list_json .= ' {
                                                        "id": "' . $idsList[$key] . '",
                                                        "_image": "' . (($has_imageList[$key] == 0) ? "no" : "yes") . '",
                                                        "_text": "' . (($has_textList[$key] == 0) ? "no" : "yes") . '",
                                                        "imageString": "",
                                                        "image_exif": "",
                                                        "selected": "no",
                                                        "text": "' . $defectList[$key] . '",
                                                        "textString": "",
                                                        "prohibitional": "' . (($is_prohibitionalList[$key] == 0) ? "no" : "yes") . '",
                                                        "safety_notes": "' . $safety_notesList[$key] . '"
                                                    }' . (($key == ($row->cnt - 1)) ? "" : ",");
                        }
                    }
                    $list_json .= ']}';
                }
                $list_json .= '},';
            }
        }

        $list_json = rtrim($list_json, ',');
        $list_json .= ']}}]}}';
        $jsonObj = json_decode($list_json);
        $minifiedJson = json_encode($jsonObj);
        return $minifiedJson;
    }

    private function getCheckinJson($vehicleCategory, $orderList, $orderListSkip, $orderListRemove, $AdBlueRequired)
    {
        $screen_no = 1;
        $action_json1 = "";
        $multiselect_json = "";
        $dynamicDefects = json_decode(env('HAVING_DYNAMIC_DEFECTS'),true);
        $startYourVehicleScreen = '
        {
            "_number": "'.$screen_no.'",
            "_type": "action",
            "regno": "",
            "title": "Start Your Vehicle Check",
            "text": "Start your vehicle and complete the checks on the following screen.",
            "buttons": {
                "show_save": "yes",
                "show_continue": "yes",
                "show_no": "no",
                "show_yes": "no"
            },
            "buttons_screen": {
                "on_continue": "'.($screen_no+1).'",
                "on_save": "'.$screen_no++.'",
                "on_no": "",
                "on_yes": ""
            },
            "defects": {},
            "options": {
                "optionList": []
            }
        }';

        $trailerAttachedJson = "";
        if(setting('is_trailer_feature_enabled')) {
            $trailerAttachedScreen = $screen_no;
            $trailerAttachedJson = '
            {
                "_number": "'.$screen_no.'",
                "_type": "confirm_with_input",
                "regno": "",
                "title": "Trailer Check",
                "text": "Is there a trailer attached to the vehicle?",
                "show_input_on": "yes",
                "input_question_text": "Enter trailer ID",
                "input_answer": "",
                "is_read_only": "yes",
                "buttons": {
                    "show_save": "yes",
                    "show_continue": "yes",
                    "show_no": "yes",
                    "show_yes": "yes"
                },
                "button_options": {
                    "show_no": "yes",
                    "show_yes": "yes"
                },
                "buttons_screen": {
                    "on_continue": "'.($screen_no+1).'",
                    "on_save": "'.$screen_no++.'",
                    "on_no": "'. $screen_no .'",
                    "on_yes": "'. $screen_no .'"
                },
                "defects": {},
                "options": {
                    "optionList": []
                }
            }';
        }

        $list_query = \DB::table('defect_master')
            ->select('order',\DB::raw("GROUP_CONCAT(TRIM(defect_order) ORDER BY defect_order DESC, defect, id SEPARATOR ',') as defect_order"), 'page_title', 'type', 'app_question','id',
                \DB::raw("COUNT(id) as cnt"),
                \DB::raw("is_prohibitional as prohibitional"),
                \DB::raw("safety_notes as safetynotes"),
                \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, defect, id SEPARATOR '|' ) as ids"),
                \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, defect, id SEPARATOR '|') as defect"),
                \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_image"),
                \DB::raw("has_image as is_has_image"),
                \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_text"),
                \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, defect, id SEPARATOR '|') as is_prohibitional"),
                \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, defect, id SEPARATOR '|') as safety_notes")
            );

        $list_query = $list_query->where('order','>=',0);
        $list_query = $list_query->whereNotIn('order', explode(',', env('TRAILER_QUESTIONS_ORDER')));
        $list_query =  $list_query->whereIn('order', explode(',', $orderList))
            ->where('for_'.$vehicleCategory,'=',1)
            ->groupBy('order');

        if(!empty($orderListSkip)){
            $list_query->whereNotIn('order', $orderListSkip);
        }

        $list_query_data = $list_query->get();
        $list_json = '{
        "status": "RoadWorthy|SafeToOperate|UnsafeToOperate",
        "screens": {
            "screen": [' . $action_json1 . ($multiselect_json ? $multiselect_json . ',' : "") . $startYourVehicleScreen . ',' . ($trailerAttachedJson ? $trailerAttachedJson . ',' : "") . '
                {
                    "_number": "'.$screen_no.'",
                    "_type": "list",
                    "regno": "",
                    "title": "Return Check",
                    "text": "Tap an item below to report a defect",
                    "buttons": {
                        "show_continue": "yes",
                        "show_save": "yes",
                        "show_no": "no",
                        "show_yes": "no"
                    },
                    "buttons_screen": {
                        "on_continue": "showReviewScreen",
                        "on_save": "'.$screen_no.'",
                        "on_no": "",
                        "on_yes": ""
                    },
                    "defects": {},
                    "options": {
                        "optionList": [
        ';

        foreach ($list_query_data as $row)
        {
            if (isset($dynamicDefects[$row->order])) {
                $isDynamicDefect = 1;
            } else {
                $isDynamicDefect = 0;
                $dynamicDefects[$row->order] = [];
            }
            $idsList = explode("|", $row->ids);
            $defectList = explode("|", $row->defect);
            $defectOrderList = explode(",", $row->defect_order);
            $dynamicDefectId = 0;
            foreach ($defectList as $key => $value) {
                if (in_array($defectOrderList[$key], $dynamicDefects[$row->order])) {
                    $dynamicDefectId = $idsList[$key];
                    break;
                }
            }
            $list_json .= '{
                    "question_type": "'.$row->type.'",
                    "_mandatory": "yes",
                    "text": "'.$row->page_title.'",
                    "defects": {
                        "title": "'.$row->page_title.'",
                        "is_having_dynamic_defects" : "'.$isDynamicDefect.'", 
                        "is_dynamic_defects_having_image" : "'.$row->is_has_image.'",
                        "is_dynamic_defect_prohibitional" : "'.$row->prohibitional.'",
                        "dynamic_defect_safety_notes" : "'.$row->safetynotes.'",
                        "dynamic_defect_id" : "' . $dynamicDefectId . '",
                        "defect_id" : "'.$row->id.'", 
                        "buttons": {
                            "show_cancel": "yes",
                            "show_continue": "yes",
                            "show_no": "no",
                            "show_save": "no",
                            "show_yes": "no"
                        },
                        "defect": [';

            $defectList = explode("|", $row->defect);
            $defectOrderList = explode(",", $row->defect_order);
            $has_imageList = explode("|", $row->has_image);
            $has_textList = explode("|", $row->has_text);
            $is_prohibitionalList = explode("|", $row->is_prohibitional);
            $safety_notesList = explode("|", $row->safety_notes);
            $idsList = explode("|", $row->ids);
            foreach ($defectList as $key => $value) {
                if (strpos(strtolower($value), 'adblue') !== false && $AdBlueRequired === 0 && $row->order == 0) {
                    continue;
                }

                if (in_array($defectOrderList[$key],$dynamicDefects[$row->order]) == false) {
                    $list_json .= ' {
                                                "id": "' . $idsList[$key] . '",
                                                "_image": "' . (($has_imageList[$key] == 0) ? "no" : "yes") . '",
                                                "_text": "' . (($has_textList[$key] == 0) ? "no" : "yes") . '",
                                                "imageString": "",
                                                "image_exif": "",
                                                "selected": "no",
                                                "text": "' . $defectList[$key] . '",
                                                "textString": "",
                                                "prohibitional": "' . (($is_prohibitionalList[$key] == 0) ? "no" : "yes") . '",
                                                "safety_notes": "' . $safety_notesList[$key] . '"
                                            }' . (($key == ($row->cnt - 1)) ? "" : ",");
                }
            }

            $list_json .= ']}';

            if($row->type == 'media_based_on_selection') {
                $list_json .= ',"media_dependent_on_answer": "yes",';
                if(strpos($row->defect, 'max_media_') !== false) {
                    $list_json .= '"maximum_allowed_image": '.str_replace('max_media_', '', $row->defect).',';
                    $list_json .= '"media_label_text": "",';
                    $list_json .= '"validation_hint_message": "",';
                } else {
                    $list_json .= '"maximum_allowed_image": 1,';
                    $list_json .= '"media_label_text": "'.$row->defect.'",';
                    $list_json .= '"validation_hint_message": "Need to capture 1 image",';
                }
                $list_json .= '"minimum_required_image": 1,';
                $list_json .= '"radio_options": '. '[
                    {
                        "key": "yes",
                        "value": "Yes"
                    },
                    {
                        "key": "no",
                        "value": "No"
                    }
                ]';
            }

            $list_json .= '},';
        }

        if(setting('is_trailer_feature_enabled')) {
            $trailerQuestionsQuery = \DB::table('defect_master')
                ->select('order',\DB::raw("GROUP_CONCAT(TRIM(defect_order) ORDER BY defect_order DESC, defect, id SEPARATOR ',') as defect_order"), 'page_title', 'type', 'app_question', 'app_question_with_defect','id',
                    \DB::raw("COUNT(id) as cnt"),
                    \DB::raw("is_prohibitional as prohibitional"),
                    \DB::raw("safety_notes as safetynotes"),
                    \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, defect, id SEPARATOR '|' ) as ids"),
                    \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, defect, id SEPARATOR '|') as defect"),
                    \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_image"),
                    \DB::raw("has_image as is_has_image"),
                    \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_text"),
                    \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, defect, id SEPARATOR '|') as is_prohibitional"),
                    \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, defect, id SEPARATOR '|') as safety_notes")
                )
                ->whereIn('order', explode(",", env('TRAILER_QUESTIONS_ORDER')))
                ->groupBy('order')
                ->get();

            foreach ($trailerQuestionsQuery as $row)
            {
                if (isset($dynamicDefects[$row->order])) {
                    $isDynamicDefect = 1;
                } else {
                    $isDynamicDefect = 0;
                    $dynamicDefects[$row->order] = [];
                }
                $idsList = explode("|", $row->ids);
                $defectList = explode("|", $row->defect);
                $defectOrderList = explode(",", $row->defect_order);
                $dynamicDefectId = 0;
                foreach ($defectList as $key => $value) {
                    if (in_array($defectOrderList[$key], $dynamicDefects[$row->order])) {
                        $dynamicDefectId = $idsList[$key];
                        break;
                    }
                }
                $list_json .= '{
                        "question_type": "'.$row->type.'",
                        "_mandatory": "yes",
                        "text": "'.$row->page_title.'",
                        "dependent_screen": "' . $trailerAttachedScreen . '",
                        "dependent_answer": "yes",
                        "related_to": "trailer",';

                if($row->type == 'media_based_on_selection') {
                    $list_json .= '"media_dependent_on_answer": "yes",';
                    $list_json .= '"maximum_allowed_image": 1,';
                    $list_json .= '"media_label_text": "'.$row->defect.'",';
                    $list_json .= '"minimum_required_image": 1,';
                    $list_json .= '"question_text": "' . $row->app_question . '",';
                    $list_json .= '"validation_hint_message": "Need to capture 1 image",';
                    $list_json .= '"radio_options": '. '[
                        {
                            "key": "yes",
                            "value": "Yes"
                        },
                        {
                            "key": "no",
                            "value": "No"
                        }
                    ]';
                }

                if($row->type == 'multiinput') {
                    $list_json .= '"inputs": ' . $row->defect . ',';
                    $list_json .= '"validation_hint_message": "Minimum of one to be completed",';
                    $list_json .= '"minimum_required_input": 1,';
                    $list_json .= '"question_text": "' . $row->app_question . '"';
                }

                if($row->type == 'media') {
                    $list_json .= '"validation_hint_message": "Need to capture min of 2 and max of 3 images",';
                    $list_json .= '"maximum_allowed_image": 3,';
                    $list_json .= '"minimum_required_image": 2,';
                    $list_json .= '"question_text": "' . $row->app_question . '"';
                }

                if($row->type == 'dropdown') {
                    $list_json .= '"dropdowns": ' . $row->defect . ',';
                    $list_json .= '"validation_hint_message": "A minimum of one dropdown has to be completed",';
                    $list_json .= '"minimum_dropdown_to_be_filled": 1,';
                    $list_json .= '"question_text": "' . $row->app_question . '"';
                }

                if($row->type == 'yesno') {
                    $list_json .= '"defects": {
                                "title": "'.$row->page_title.'",
                                "is_having_dynamic_defects" : "'.$isDynamicDefect.'", 
                                "is_dynamic_defects_having_image" : "'.$row->is_has_image.'",
                                "is_dynamic_defect_prohibitional" : "'.$row->prohibitional.'",
                                "dynamic_defect_safety_notes" : "'.$row->safetynotes.'",
                                "dynamic_defect_id" : "' . $dynamicDefectId . '", 
                                "defect_id" : "'.$row->id.'", 
                                "buttons": {
                                    "show_cancel": "yes",
                                    "show_continue": "yes",
                                    "show_no": "no",
                                    "show_save": "no",
                                    "show_yes": "no"
                                },
                                "defect": [';

                    $defectList = explode("|", $row->defect);
                    $defectOrderList = explode(",", $row->defect_order);
                    $has_imageList = explode("|", $row->has_image);
                    $has_textList = explode("|", $row->has_text);
                    $is_prohibitionalList = explode("|", $row->is_prohibitional);
                    $safety_notesList = explode("|", $row->safety_notes);
                    $idsList = explode("|", $row->ids);
                    foreach ($defectList as $key => $value) {

                        if (in_array($defectOrderList[$key],$dynamicDefects[$row->order]) == false) {
                            $list_json .= ' {
                                                        "id": "' . $idsList[$key] . '",
                                                        "_image": "' . (($has_imageList[$key] == 0) ? "no" : "yes") . '",
                                                        "_text": "' . (($has_textList[$key] == 0) ? "no" : "yes") . '",
                                                        "imageString": "",
                                                        "image_exif": "",
                                                        "selected": "no",
                                                        "text": "' . $defectList[$key] . '",
                                                        "textString": "",
                                                        "prohibitional": "' . (($is_prohibitionalList[$key] == 0) ? "no" : "yes") . '",
                                                        "safety_notes": "' . $safety_notesList[$key] . '"
                                                    }' . (($key == ($row->cnt - 1)) ? "" : ",");
                        }
                    }
                    $list_json .= ']
                                }';
                }
                $list_json .= '},';
            }
        }

        $list_json = rtrim($list_json, ',');
        $list_json .= ']}}]}}';
        $jsonObj = json_decode($list_json);
        $minifiedJson = json_encode($jsonObj);
        return $minifiedJson;
    }

    private function getDefectJson($vehicleCategory, $orderList, $orderListSkip, $orderListRemove, $AdBlueRequired)
    {
        $screen_no = 1;
        $trailerAttachedJson = "";
        $dynamicDefects = json_decode(env('HAVING_DYNAMIC_DEFECTS'),true);
        if(setting('is_trailer_feature_enabled')) {
            $trailerAttachedScreen = $screen_no;
            $trailerAttachedJson = '
            {
                "_number": "'.$screen_no.'",
                "_type": "confirm_with_input",
                "regno": "",
                "title": "Trailer Check",
                "text": "Is there a trailer attached to the vehicle?",
                "show_input_on": "yes",
                "input_question_text": "Enter trailer ID",
                "input_answer": "",
                "is_read_only": "yes",
                "buttons": {
                    "show_save": "yes",
                    "show_continue": "yes",
                    "show_no": "yes",
                    "show_yes": "yes"
                },
                "button_options": {
                    "show_no": "yes",
                    "show_yes": "yes"
                },
                "buttons_screen": {
                    "on_continue": "'.($screen_no+1).'",
                    "on_save": "'.$screen_no++.'",
                    "on_no": "'. $screen_no .'",
                    "on_yes": "'. $screen_no .'"
                },
                "defects": {},
                "options": {
                    "optionList": []
                }
            }';
        }

        $list_query = \DB::table('defect_master')
            ->select('order',\DB::raw("GROUP_CONCAT(TRIM(defect_order) ORDER BY defect_order DESC, defect, id SEPARATOR ',') as defect_order"), 'page_title', 'type', 'app_question','id',
                \DB::raw("COUNT(id) as cnt"),
                \DB::raw("is_prohibitional as prohibitional"),
                \DB::raw("safety_notes as safetynotes"),
                \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, defect, id SEPARATOR '|' ) as ids"),
                \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, defect, id SEPARATOR '|') as defect"),
                \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_image"),
                \DB::raw("has_image as is_has_image"),
                \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_text"),
                \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, defect, id SEPARATOR '|') as is_prohibitional"),
                \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, defect, id SEPARATOR '|') as safety_notes")
            )
            ->where('order','>=',0);

        if(!empty($orderListRemove)) {
            $list_query = $list_query->whereNotIn('order', $orderListRemove);
        }

        $list_query = $list_query->whereNotIn('order', explode(',', env('TRAILER_QUESTIONS_ORDER')));

        $list_query = $list_query->whereIn('order', explode(',', $orderList))
            ->where('for_'.$vehicleCategory,'=',1)
            ->groupBy('order');

        if(!empty($orderListSkip)){
            $list_query = $list_query->whereNotIn('order', $orderListSkip);
        }

        $list_query_data = $list_query->get();
        $list_json = '{
        "status": "RoadWorthy|SafeToOperate|UnsafeToOperate",
        "screens": {
            "screen": [' . ($trailerAttachedJson ? $trailerAttachedJson . ',' : "") .
            '{
                    "_number": "' . $screen_no . '",
                    "_type": "list",
                    "regno": "",
                    "title": "Report Defect",
                    "text": "Tap an item below to report a defect",
                    "buttons": {
                        "show_continue": "yes",
                        "show_save": "yes",
                        "show_no": "no",
                        "show_yes": "no"
                    },
                    "buttons_screen": {
                        "on_continue": "showReviewScreen",
                        "on_save": "1",
                        "on_no": "",
                        "on_yes": ""
                    },
                    "defects": {},
                    "options": {
                        "optionList": [
        ';


        foreach ($list_query_data as $row)
        {
            if (isset($dynamicDefects[$row->order])) {
                $isDynamicDefect = 1;
            } else {
                $isDynamicDefect = 0;
                $dynamicDefects[$row->order] = [];
            }
            $idsList = explode("|", $row->ids);
            $defectList = explode("|", $row->defect);
            $defectOrderList = explode(",", $row->defect_order);
            $dynamicDefectId = 0;
            foreach ($defectList as $key => $value) {
                if (in_array($defectOrderList[$key], $dynamicDefects[$row->order])) {
                    $dynamicDefectId = $idsList[$key];
                    break;
                }
            }
            $list_json .= '{
                    "_mandatory": "yes",
                    "text": "'.$row->page_title.'",
                    "defects": {
                        "title": "'.$row->page_title.'",
                        "is_having_dynamic_defects" : "'.$isDynamicDefect.'",
                        "is_dynamic_defects_having_image" : "'.$row->is_has_image.'",
                        "is_dynamic_defect_prohibitional" : "'.$row->prohibitional.'",
                        "dynamic_defect_safety_notes" : "'.$row->safetynotes.'",
                        "dynamic_defect_id" : "' . $dynamicDefectId . '",  
                        "defect_id" : "'.$row->id.'", 
                        "buttons": {
                            "show_cancel": "yes",
                            "show_continue": "yes",
                            "show_no": "no",
                            "show_save": "no",
                            "show_yes": "no"
                        },
                        "defect": [';
            $defectList = explode("|", $row->defect);
            $defectOrderList = explode(",", $row->defect_order);
            $has_imageList = explode("|", $row->has_image);
            $has_textList = explode("|", $row->has_text);
            $is_prohibitionalList = explode("|", $row->is_prohibitional);
            $safety_notesList = explode("|", $row->safety_notes);
            $idsList = explode("|", $row->ids);
            foreach ($defectList as $key => $value) {
                if (strpos(strtolower($value), 'adblue') !== false && $AdBlueRequired === 0 && $row->order == 0) {
                    continue;
                }

                if (in_array($defectOrderList[$key],$dynamicDefects[$row->order]) == false) {
                    $list_json .= ' {
                                                "id": "' . $idsList[$key] . '",
                                                "_image": "' . (($has_imageList[$key] == 0) ? "no" : "yes") . '",
                                                "_text": "' . (($has_textList[$key] == 0) ? "no" : "yes") . '",
                                                "imageString": "",
                                                "image_exif": "",
                                                "selected": "no",
                                                "text": "' . $defectList[$key] . '",
                                                "textString": "",
                                                "prohibitional": "' . (($is_prohibitionalList[$key] == 0) ? "no" : "yes") . '",
                                                "safety_notes": "' . $safety_notesList[$key] . '"
                                            }' . (($key == ($row->cnt - 1)) ? "" : ",");
                }
            }
            $list_json .= ']
                        }
                    },';
        }

        $skipDefectsJson = "";
        if(count($orderListSkip) > 0) {
            $skipDefectsQuestionsQuery = \DB::table('defect_master')
                ->select('order',\DB::raw("GROUP_CONCAT(TRIM(defect_order) ORDER BY defect_order DESC, defect, id SEPARATOR ',') as defect_order"), 'page_title', 'type', 'app_question','id',
                    \DB::raw("COUNT(id) as cnt"),
                    \DB::raw("is_prohibitional as prohibitional"),
                    \DB::raw("safety_notes as safetynotes"),
                    \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, defect, id SEPARATOR '|' ) as ids"),
                    \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, defect, id SEPARATOR '|') as defect"),
                    \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_image"),
                    \DB::raw("has_image as is_has_image"),
                    \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_text"),
                    \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, defect, id SEPARATOR '|') as is_prohibitional"),
                    \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, defect, id SEPARATOR '|') as safety_notes")
                )
                ->whereIn('order', $orderListSkip);

            if(!empty($orderListRemove)) {
                $skipDefectsQuestionsQuery = $skipDefectsQuestionsQuery->whereNotIn('order', $orderListRemove);
            }
            $skipDefectsQuestionsQuery = $skipDefectsQuestionsQuery->groupBy('order')->get();

            foreach ($skipDefectsQuestionsQuery as $row)
            {
                if (isset($dynamicDefects[$row->order])) {
                    $isDynamicDefect = 1;
                } else {
                    $isDynamicDefect = 0;
                    $dynamicDefects[$row->order] = [];
                }
                $idsList = explode("|", $row->ids);
                $defectList = explode("|", $row->defect);
                $defectOrderList = explode(",", $row->defect_order);
                $dynamicDefectId = 0;
                foreach ($defectList as $key => $value) {
                    if (in_array($defectOrderList[$key], $dynamicDefects[$row->order])) {
                        $dynamicDefectId = $idsList[$key];
                        break;
                    }
                }
                $list_json .= '{
                        "_mandatory": "yes",
                        "text": "'.$row->page_title.'",
                        "defects": {
                            "title": "'.$row->page_title.'",
                            "is_having_dynamic_defects" : "'.$isDynamicDefect.'",
                            "is_dynamic_defects_having_image" : "'.$row->is_has_image.'",
                            "is_dynamic_defect_prohibitional" : "'.$row->prohibitional.'",
                            "dynamic_defect_safety_notes" : "'.$row->safetynotes.'",
                            "dynamic_defect_id" : "' . $dynamicDefectId . '",  
                            "defect_id" : "'.$row->id.'", 
                            "buttons": {
                                "show_cancel": "yes",
                                "show_continue": "yes",
                                "show_no": "no",
                                "show_save": "no",
                                "show_yes": "no"
                            },
                            "defect": [';
                $defectList = explode("|", $row->defect);
                $defectOrderList = explode(",", $row->defect_order);
                $has_imageList = explode("|", $row->has_image);
                $has_textList = explode("|", $row->has_text);
                $is_prohibitionalList = explode("|", $row->is_prohibitional);
                $safety_notesList = explode("|", $row->safety_notes);
                $idsList = explode("|", $row->ids);
                foreach ($defectList as $key => $value) {
                    if (in_array($defectOrderList[$key],$dynamicDefects[$row->order]) == false) {
                        $list_json .= ' {
                                                    "id": "' . $idsList[$key] . '",
                                                    "_image": "' . (($has_imageList[$key] == 0) ? "no" : "yes") . '",
                                                    "_text": "' . (($has_textList[$key] == 0) ? "no" : "yes") . '",
                                                    "imageString": "",
                                                    "image_exif": "",
                                                    "selected": "no",
                                                    "text": "' . $defectList[$key] . '",
                                                    "textString": "",
                                                    "prohibitional": "' . (($is_prohibitionalList[$key] == 0) ? "no" : "yes") . '",
                                                    "safety_notes": "' . $safety_notesList[$key] . '"
                                                }' . (($key == ($row->cnt - 1)) ? "" : ",");
                    }
                }
                $list_json .= ']
                            }
                        },';
            }
        }

        $trailerQuestionsJson = "";
        if(setting('is_trailer_feature_enabled')) {
            $trailerQuestionsQuery = \DB::table('defect_master')
                ->select('order',\DB::raw("GROUP_CONCAT(TRIM(defect_order) ORDER BY defect_order DESC, defect, id SEPARATOR ',') as defect_order"), 'page_title', 'type', 'app_question','id',
                    \DB::raw("COUNT(id) as cnt"),
                    \DB::raw("is_prohibitional as prohibitional"),
                    \DB::raw("safety_notes as safetynotes"),
                    \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, defect, id SEPARATOR '|' ) as ids"),
                    \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, defect, id SEPARATOR '|') as defect"),
                    \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_image"),
                    \DB::raw("has_image as is_has_image"),
                    \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, defect, id SEPARATOR '|') as has_text"),
                    \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, defect, id SEPARATOR '|') as is_prohibitional"),
                    \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, defect, id SEPARATOR '|') as safety_notes")
                )
                ->whereIn('order', explode(",", env('TRAILER_QUESTIONS_ORDER')))
                ->groupBy('order')
                ->get();

            foreach ($trailerQuestionsQuery as $row)
            {
                if($row->type != "yesno") {
                    continue;
                }
                if (isset($dynamicDefects[$row->order])) {
                    $isDynamicDefect = 1;
                } else {
                    $isDynamicDefect = 0;
                    $dynamicDefects[$row->order] = [];
                }
                $idsList = explode("|", $row->ids);
                $defectList = explode("|", $row->defect);
                $defectOrderList = explode(",", $row->defect_order);
                $dynamicDefectId = 0;
                foreach ($defectList as $key => $value) {
                    if (in_array($defectOrderList[$key], $dynamicDefects[$row->order])) {
                        $dynamicDefectId = $idsList[$key];
                        break;
                    }
                }
                $list_json .= '{
                        "_mandatory": "yes",
                        "text": "'.$row->page_title.'",
                        "dependent_screen": "' . $trailerAttachedScreen . '",
                        "dependent_answer": "yes",
                        "related_to": "trailer",
                        "defects": {
                            "title": "'.$row->page_title.'",
                            "is_having_dynamic_defects" : "'.$isDynamicDefect.'", 
                            "is_dynamic_defects_having_image" : "'.$row->is_has_image.'",
                            "is_dynamic_defect_prohibitional" : "'.$row->prohibitional.'",
                            "dynamic_defect_safety_notes" : "'.$row->safetynotes.'",
                            "dynamic_defect_id" : "' . $dynamicDefectId . '", 
                            "defect_id" : "'.$row->id.'", 
                            "buttons": {
                                "show_cancel": "yes",
                                "show_continue": "yes",
                                "show_no": "no",
                                "show_save": "no",
                                "show_yes": "no"
                            },
                            "defect": [';
                $defectList = explode("|", $row->defect);
                $defectOrderList = explode(",", $row->defect_order);
                $has_imageList = explode("|", $row->has_image);
                $has_textList = explode("|", $row->has_text);
                $is_prohibitionalList = explode("|", $row->is_prohibitional);
                $safety_notesList = explode("|", $row->safety_notes);
                $idsList = explode("|", $row->ids);
                foreach ($defectList as $key => $value) {
                    if (in_array($defectOrderList[$key],$dynamicDefects[$row->order]) == false) {
                        $list_json .= ' {
                                                    "id": "' . $idsList[$key] . '",
                                                    "_image": "' . (($has_imageList[$key] == 0) ? "no" : "yes") . '",
                                                    "_text": "' . (($has_textList[$key] == 0) ? "no" : "yes") . '",
                                                    "imageString": "",
                                                    "image_exif": "",
                                                    "selected": "no",
                                                    "text": "' . $defectList[$key] . '",
                                                    "textString": "",
                                                    "prohibitional": "' . (($is_prohibitionalList[$key] == 0) ? "no" : "yes") . '",
                                                    "safety_notes": "' . $safety_notesList[$key] . '"
                                                }' . (($key == ($row->cnt - 1)) ? "" : ",");
                    }
                }
                $list_json .= ']
                            }
                        },';
            }
        }

        $list_json = rtrim($list_json, ',');
        $list_json .= ']}}]}}';

        $jsonObj = json_decode($list_json);
        $minifiedJson = json_encode($jsonObj);
        return $minifiedJson;
    }
}
