<?php

use Illuminate\Database\Seeder;
use App\Models\DefectMaster;
use App\Models\DefectMasterVehicleTypes;

class AddVehicleConditionQuestionInDefectMasterSeerder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $onlyBrands = ['ferns'];

        if(!empty($onlyBrands) && in_array(env('BRAND_NAME'), $onlyBrands)) {
            $maxOrder = DefectMaster::max('order');
            $defectOrder = $maxOrder + 1;
            $defectMaster = new DefectMaster();
            $defectMaster->order = $defectOrder;
            $defectMaster->type = 'media_based_on_selection';
            $defectMaster->page_title = 'Vehicle Condition';
            $defectMaster->app_question = "Would you like to take any photos of the vehicle's general condition?";
            $defectMaster->app_question_with_defect = "Would you like to take any photos of the vehicle's general condition?";
            $defectMaster->defect = 'max_media_16';
            $defectMaster->has_not_applicable_option = 0;
            $defectMaster->defect_order = 0;
            $defectMaster->has_image = 0;
            $defectMaster->has_text = 0;
            $defectMaster->is_prohibitional = 0;
            $defectMaster->show_warning = 0;
            $defectMaster->warning_text = NULL;
            $defectMaster->safety_notes = 'Roadworthy but needs workshop visit. Confirm with manager';
            $defectMaster->for_hgv = 1;
            $defectMaster->{"for_non-hgv"} = 1;
            $defectMaster->save();

            $allDefectMasterVehicleTypes = DefectMasterVehicleTypes::all();
            foreach($allDefectMasterVehicleTypes as $defectMasterVehicleTypes) {
                $defectMasterVehicleTypes->defect_list = $defectMasterVehicleTypes->defect_list.",".$defectOrder;
                $defectMasterVehicleTypes->save();
            }
        }
    }
}
