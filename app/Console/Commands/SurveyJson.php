<?php

namespace App\Console\Commands;

use Storage;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/*
php artisan survey:json checkout hgv all 1
php artisan survey:json checkin hgv all 2
php artisan survey:json defect hgv all 3
php artisan survey:json checkout non-hgv all 4
php artisan survey:json checkin non-hgv all 5
php artisan survey:json defect non-hgv all 6
php artisan survey:json checkout non-hgv parcelvan 7
php artisan survey:json checkin non-hgv parcelvan 8
php artisan survey:json defect non-hgv parcelvan 9
*/

class SurveyJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:json {type : checkout|checkin|defect} {category : hgv|non-hgv} {vehicletype=all} {survey_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to create the Survey Master json.';

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
        $typeArray = ['checkout','checkin','defect'];
        $vehicleCategoryArray = ['hgv','non-hgv'];

        $jsonType = $this->argument('type');
        if (!in_array($jsonType, $typeArray)){
            $this->error('Invalid Type');
            $this->info("Please Enter valid Type form : [checkout|checkin|defect]");
            exit;
        }
        $vehicleCategory = $this->argument('category');
        if (!in_array($vehicleCategory, $vehicleCategoryArray)){
            $this->error("Invalid Vehicle Category");
            $this->info("Please Enter valid Vehicle Category from : [hgv|non-hgv]");
            exit;
        }
        $vehicletype = $this->argument('vehicletype');
        $survey_id = $this->argument('survey_id');
        $this->info('Generating Json for : ' . $jsonType . " [" . $vehicleCategory . "]");
        if(strtolower($jsonType) == "checkout"){
            $this->genCheckout($vehicleCategory, $vehicletype, $survey_id);
        }
        elseif(strtolower($jsonType) == "checkin"){
            $this->genCheckin($vehicleCategory, $vehicletype, $survey_id);
        }
        elseif(strtolower($jsonType) == "defect"){
            $this->genDefect($vehicleCategory, $vehicletype, $survey_id);
        }
    }

    private function genCheckout($vehicleCategory, $vehicletype, $survey_id)
    {
        $screen_no = 1;
        
        $action_json1 = '
        {
            "_number": "'.$screen_no.'",
            "_type": "action",
            "regno": "",
            "title": "Pre-start Checks",
            "text": "Turn on ignition and make sure that your vehicle\'s levels are sufficient.\\\n\\\nComplete the checks on the following screen.",
            "answer": "",
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

        $multiselect_query = \DB::table('defect_master')
            ->select('order', 'page_title', 'app_question', 
                    \DB::raw("COUNT(id) as cnt"),
                    \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, id SEPARATOR '|' ) as ids"),
                    \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, id SEPARATOR '|') as defect"),
                    \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, id SEPARATOR '|') as has_image"),
                    \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, id SEPARATOR '|') as has_text"),
                    \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, id SEPARATOR '|') as is_prohibitional"),
                    \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, id SEPARATOR '|') as safety_notes")
                )
        ->where('order','=',0)
        ->where('for_'.$vehicleCategory,'=',1)
        ->groupBy('order');

        $multiselect_query_data = $multiselect_query->get();
        $multiselect_json = "";
        foreach ($multiselect_query_data as $row) {
            // print_r($row);

            $multiselect_json .= '
                {
                "_number": "'.$screen_no.'",
                "_type": "multiselect",
                "regno": "",
                "title": "'.$row->page_title.'",
                "text": "",
                "answer": "",
                "buttons": {
                "show_continue": "yes",
                "show_save": "yes",
                "show_no": "no",
                "show_yes": "no"
                },
                "buttons_screen": {
                "on_continue": "'.($screen_no + 1).'",
                "on_save": "'.($screen_no++).'",
                "on_no": "",
                "on_yes": ""
                },
                "defects": {},
                "options": {
                "optionList": [';

            $defectList = explode("|", $row->defect);
            $idsList = explode("|", $row->ids);
            foreach ($defectList as $key => $value) {
                $multiselect_json .= '{
                    "id": "'.$idsList[$key].'",
                    "_mandatory": "yes",
                    "text": "'.$defectList[$key].'",
                    "answer": ""
                    }'. (($key == ($row->cnt - 1))?"":",") . '
                ';
            }
            $multiselect_json .= ']}}';
        }
        // echo "\n".$multiselect_json . "\n";
        $action_json = '
        {
            "_number": "'.$screen_no.'",
            "_type": "action",
            "regno": "",
            "title": "Start Your Vehicle",
            "text": "Start your vehicle and commence the walk-around checks over the following screens.",
            "answer": "",
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

        $yesno_query = \DB::table('defect_master')
            ->select('order', 'page_title', 'app_question', 
                    \DB::raw("COUNT(id) as cnt"),
                    \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, id SEPARATOR '|' ) as ids"),
                    \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, id SEPARATOR '|') as defect"),
                    \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, id SEPARATOR '|') as has_image"),
                    \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, id SEPARATOR '|') as has_text"),
                    \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, id SEPARATOR '|') as is_prohibitional"),
                    \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, id SEPARATOR '|') as safety_notes")
                )
        ->where('order','>',0)
        ->whereNotIn('order', [22, 23, 24, 25, 26])
        ->where('for_'.$vehicleCategory,'=',1)
        ->groupBy('order');

        if ($vehicletype == "parcelvan"){
            $yesno_query->orWhere('order','=',11);
        }

        $yesno_query_data = $yesno_query->get();
        // print_r($query_data);
        $yesno_json = "";
        foreach ($yesno_query_data as $row) {
            $yesno_json .= '
                {
                    "_number": "'.$screen_no.'",
                    "_type": "yesno",
                    "regno": "",
                    "title": "'.$row->page_title.'",
                    "text": "'.$row->app_question.'",
                    "answer": "",
                    "buttons": {
                        "show_save": "yes",
                        "show_continue": "no",
                        "show_no": "yes",
                        "show_yes": "yes"
                    },
                    "buttons_screen": {
                        "on_continue": "",
                        "on_no": "showDefectDialog",
                        "on_yes": "'.($screen_no + 1).'",
                        "on_save": "'.($screen_no++).'"
                    },
                    "defects": {
                        "title": "'.$row->page_title.'",
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
            $has_imageList = explode("|", $row->has_image);
            $has_textList = explode("|", $row->has_text);
            $is_prohibitionalList = explode("|", $row->is_prohibitional);
            $safety_notesList = explode("|", $row->safety_notes);
            $idsList = explode("|", $row->ids);
            foreach ($defectList as $key => $value) {
                $yesno_json .= '{
                                    "id": "'.$idsList[$key].'",
                                    "_image": "'.(($has_imageList[$key] == 0)?"no":"yes").'",
                                    "_text": "'.(($has_textList[$key] == 0)?"no":"yes").'",
                                    "imageString": "",
                                    "image_exif": "",
                                    "selected": "no",
                                    "text": "'.$defectList[$key].'",
                                    "textString": "",
                                    "prohibitional": "'.(($is_prohibitionalList[$key] == 0)?"no":"yes").'",
                                    "safety_notes": "'.$safety_notesList[$key].'"
                                }'. (($key == ($row->cnt - 1))?"":",");
            }
            $yesno_json .= ']
                        },
                        "options": {
                            "optionList": []
                        }},';
        }
        $yesno_json = rtrim($yesno_json, ',');
        $final_json = '{
            "status": "RoadWorthy|SafeToOperate|UnsafeToOperate",
            "screens": {
                "screen": [' . $action_json1 . "," . $multiselect_json . "," . $action_json . "," . $yesno_json . ']}}';
                // str_replace(search, replace, subject)
        $final_json = str_replace('"on_yes": "'.$screen_no.'"', '"on_yes": "showReviewScreen"', $final_json);
        // print_r($final_json);

        $jsonObj = json_decode($final_json);
        $minifiedJson = json_encode($jsonObj);
        $minifiedJson = str_replace("'", "\'", $minifiedJson);
        Storage::put('checkout_'.$vehicleCategory.'_'.$vehicletype.'.json',$minifiedJson);
        $update_query = "UPDATE survey_master SET screen_json='".$minifiedJson."' WHERE id=$survey_id;";
        Storage::append('all_json.sql',$update_query);
    }

    private function genCheckin($vehicleCategory, $vehicletype, $survey_id)
    {
        $screen_no = 1;
        
        $action_json1 = '
        {
            "_number": "'.$screen_no.'",
            "_type": "action",
            "regno": "",
            "title": "Pre-start Checks",
            "text": "Turn on ignition and make sure that your vehicle\'s levels are sufficient.\\\n\\\nComplete the checks on the following screen.",
            "answer": "",
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
        },';

        $multiselect_query = \DB::table('defect_master')
            ->select('order', 'page_title', 'app_question', 
                    \DB::raw("COUNT(id) as cnt"),
                    \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, id SEPARATOR '|' ) as ids"),
                    \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, id SEPARATOR '|') as defect"),
                    \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, id SEPARATOR '|') as has_image"),
                    \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, id SEPARATOR '|') as has_text"),
                    \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, id SEPARATOR '|') as is_prohibitional"),
                    \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, id SEPARATOR '|') as safety_notes")
                )
        ->where('order','=',0)
        ->where('for_'.$vehicleCategory,'=',1)
        ->groupBy('order');

        $multiselect_query_data = $multiselect_query->get();
        $multiselect_json = "";
        foreach ($multiselect_query_data as $row) {
            // print_r($row);
            $row->page_title = "Level Checks";
            $multiselect_json .= '
                {
                "_number": "'.$screen_no.'",
                "_type": "multiselect",
                "regno": "",
                "title": "'.$row->page_title.'",
                "text": "",
                "answer": "",
                "buttons": {
                "show_continue": "yes",
                "show_save": "yes",
                "show_no": "no",
                "show_yes": "no"
                },
                "buttons_screen": {
                "on_continue": "'.($screen_no + 1).'",
                "on_save": "'.($screen_no++).'",
                "on_no": "",
                "on_yes": ""
                },
                "defects": {},
                "options": {
                "optionList": [';

            $defectList = explode("|", $row->defect);
            $idsList = explode("|", $row->ids);
            foreach ($defectList as $key => $value) {
                $multiselect_json .= '{
                    "id": "'.$idsList[$key].'",
                    "_mandatory": "yes",
                    "text": "'.$defectList[$key].'",
                    "answer": ""
                    }'. (($key == ($row->cnt - 1))?"":",") . '
                ';
            }
            $multiselect_json .= ']}}';
        }

        $list_query = \DB::table('defect_master')
            ->select('order', 'page_title', 'app_question', 
                    \DB::raw("COUNT(id) as cnt"),
                    \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, id SEPARATOR '|' ) as ids"),
                    \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, id SEPARATOR '|') as defect"),
                    \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, id SEPARATOR '|') as has_image"),
                    \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, id SEPARATOR '|') as has_text"),
                    \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, id SEPARATOR '|') as is_prohibitional"),
                    \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, id SEPARATOR '|') as safety_notes")
                )
        ->where('order','>',0)
        ->whereNotIn('order', [22, 23, 24, 25, 26])
        ->where('for_'.$vehicleCategory,'=',1)
        ->groupBy('order');
        
        if ($vehicletype == "parcelvan"){
            $list_query->orWhere('order','=',11);
        }

        $list_query_data = $list_query->get();
        $list_json = '{
        "status": "RoadWorthy|SafeToOperate|UnsafeToOperate",
        "screens": {
            "screen": [' . $action_json1 . $multiselect_json . ',
                {
                    "_number": "'.$screen_no.'",
                    "_type": "list",
                    "regno": "",
                    "title": "Return Check",
                    "text": "Tap an item below to report a defect",
                    "answer": "",
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
            $list_json .= '{
                    "_mandatory": "yes",
                    "text": "'.$row->page_title.'",
                    "defects": {
                        "title": "'.$row->page_title.'",
                        "buttons": {
                            "show_cancel": "yes",
                            "show_continue": "yes",
                            "show_no": "no",
                            "show_save": "no",
                            "show_yes": "no"
                        },
                        "defect": [';
                        
                        $defectList = explode("|", $row->defect);
                        $has_imageList = explode("|", $row->has_image);
                        $has_textList = explode("|", $row->has_text);
                        $is_prohibitionalList = explode("|", $row->is_prohibitional);
                        $safety_notesList = explode("|", $row->safety_notes);
                        $idsList = explode("|", $row->ids);
                        foreach ($defectList as $key => $value) {
                            $list_json .= ' {
                                                "id": "'.$idsList[$key].'",
                                                "_image": "'.(($has_imageList[$key] == 0)?"no":"yes").'",
                                                "_text": "'.(($has_textList[$key] == 0)?"no":"yes").'",
                                                "imageString": "",
                                                "image_exif": "",
                                                "selected": "no",
                                                "text": "'.$defectList[$key].'",
                                                "textString": "",
                                                "prohibitional": "'.(($is_prohibitionalList[$key] == 0)?"no":"yes").'",
                                                "safety_notes": "'.$safety_notesList[$key].'"
                                            }'. (($key == ($row->cnt - 1))?"":",");
                        }
            $list_json .= ']
                        }
                    },';
        }
        $list_json = rtrim($list_json, ',');
        $list_json .= ']}}]}}';
        // print_r($list_json);
        $jsonObj = json_decode($list_json);
        $minifiedJson = json_encode($jsonObj);
        $minifiedJson = str_replace("'", "\'", $minifiedJson);
        Storage::put('checkin_'.$vehicleCategory.'_'.$vehicletype.'.json',$minifiedJson);
        $update_query = "UPDATE survey_master SET screen_json='".$minifiedJson."' WHERE id=$survey_id;";
        Storage::append('all_json.sql',$update_query);
    }

    private function genDefect($vehicleCategory, $vehicletype, $survey_id)
    {
        $list_query = \DB::table('defect_master')
            ->select('order', 'page_title', 'app_question', 
                    \DB::raw("COUNT(id) as cnt"),
                    \DB::raw("GROUP_CONCAT(id ORDER BY defect_order DESC, id SEPARATOR '|' ) as ids"),
                    \DB::raw("GROUP_CONCAT(TRIM(defect) ORDER BY defect_order DESC, id SEPARATOR '|') as defect"),
                    \DB::raw("GROUP_CONCAT( has_image ORDER BY defect_order DESC, id SEPARATOR '|') as has_image"),
                    \DB::raw("GROUP_CONCAT( has_text ORDER BY defect_order DESC, id SEPARATOR '|') as has_text"),
                    \DB::raw("GROUP_CONCAT( is_prohibitional ORDER BY defect_order DESC, id SEPARATOR '|') as is_prohibitional"),
                    \DB::raw("GROUP_CONCAT( safety_notes ORDER BY defect_order DESC, id SEPARATOR '|') as safety_notes")
                )
        ->where('order','>',0)
        ->where('for_'.$vehicleCategory,'=',1)
        ->groupBy('order');
        
        if ($vehicletype == "parcelvan"){
            $list_query->orWhere('order','=',11);
        }

        $list_query_data = $list_query->get();
        $list_json = '{
        "status": "RoadWorthy|SafeToOperate|UnsafeToOperate",
        "screens": {
            "screen": [
                {
                    "_number": "1",
                    "_type": "list",
                    "regno": "",
                    "title": "Report Defect",
                    "text": "Tap an item below to report a defect",
                    "answer": "",
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
            $list_json .= '{
                    "_mandatory": "yes",
                    "text": "'.$row->page_title.'",
                    "defects": {
                        "title": "'.$row->page_title.'",
                        "buttons": {
                            "show_cancel": "yes",
                            "show_continue": "yes",
                            "show_no": "no",
                            "show_save": "no",
                            "show_yes": "no"
                        },
                        "defect": [';
                        
                        $defectList = explode("|", $row->defect);
                        $has_imageList = explode("|", $row->has_image);
                        $has_textList = explode("|", $row->has_text);
                        $is_prohibitionalList = explode("|", $row->is_prohibitional);
                        $safety_notesList = explode("|", $row->safety_notes);
                        $idsList = explode("|", $row->ids);
                        foreach ($defectList as $key => $value) {
                            $list_json .= ' {
                                                "id": "'.$idsList[$key].'",
                                                "_image": "'.(($has_imageList[$key] == 0)?"no":"yes").'",
                                                "_text": "'.(($has_textList[$key] == 0)?"no":"yes").'",
                                                "imageString": "",
                                                "image_exif": "",
                                                "selected": "no",
                                                "text": "'.$defectList[$key].'",
                                                "textString": "",
                                                "prohibitional": "'.(($is_prohibitionalList[$key] == 0)?"no":"yes").'",
                                                "safety_notes": "'.$safety_notesList[$key].'"
                                            }'. (($key == ($row->cnt - 1))?"":",");
                        }
            $list_json .= ']
                        }
                    },';
        }
        $list_json = rtrim($list_json, ',');            
        $list_json .= ']}}]}}';

        $jsonObj = json_decode($list_json);
        $minifiedJson = json_encode($jsonObj);
        $minifiedJson = str_replace("'", "\'", $minifiedJson);
        // print_r($list_json);
        Storage::put('defect_'.$vehicleCategory.'_'.$vehicletype.'.json',$minifiedJson);
        $update_query = "UPDATE survey_master SET screen_json='".$minifiedJson."' WHERE id=$survey_id;";
        Storage::append('all_json.sql',$update_query);
    }
}
