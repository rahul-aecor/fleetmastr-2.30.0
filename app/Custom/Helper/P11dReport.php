<?php
namespace App\Custom\Helper;
use Mail;
use Exception;
use Carbon\Carbon as Carbon;
use App\Models\P11dReport;
class P11dReportHelper {
    function toExcel($excelFileDetail, $sheetArray, $output='xlsx', $download='yes'){
        $fileName=strtolower(str_replace(" ","-",$excelFileDetail['title']))."-".time();
        $excelCreateObj = \Excel::create($fileName, function($excel) use($excelFileDetail, $sheetArray) {
            $excel->setTitle($excelFileDetail['title']);
            foreach ($sheetArray as $sheetDetail) {
                $excel->sheet($sheetDetail['otherParams']['sheetName'], function($sheet) use($sheetDetail) {
                    $sheet->row(1, $sheetDetail['labelArray']);
                    $sheet->row(1, function($row){
                        $row->setBackground("#FFFFFF");
                        $row->setFontColor('#000000');
                        $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(10);
                    });
                    if(count($sheetDetail['columnFormat'])>0){
                        $sheet->setColumnFormat($sheetDetail['columnFormat']);
                    }
                    $rowNo = 2;
                    for($i=0;$i<count($sheetDetail['dataArray']);$i++) {
                        $sheet->row($rowNo, $sheetDetail['dataArray'][$i]);
                        print_r('expression');exit;
                        if(isset($sheetDetail['cellBackgroundArray'])) {
                            $column = 'A';
                            for($j=0;$j<count($sheetDetail['dataArray'][$i]);$j++) {
                                $background=$sheetDetail['cellBackgroundArray'][$i][$j];
                                if($background!="") {
                                    $sheet->cell($column.$rowNo, function($cell) use($background) {
                                        $cell->setBackground(strtoupper($background));
                                    });
                                }
                                $sheet->cell($column.$rowNo, function($cell) use($background) {
                                        $cell->setAlignment('center');
                                    });
                                        
                                $column++;
                            }
                        }
                        
                        $rowNo++;
                    }
                    $sheet->setAutoFilter();                        
                    if(isset($sheetDetail['otherParams']['freezePane'])) {
                        $sheet->setFreeze($sheetDetail['otherParams']['freezePane']);
                    }
                });
            }
        });
        if($download == 'yes'){
            $excelCreateObj->export($output);
        }else{
            $excelCreateObj->store($output);
        }
        $exportFile=storage_path('exports').'/'.$fileName.'.xlsx';
        print_r($exportFile);
        return $exportFile;
    }
    function colorMix($color_1 = array(0, 0, 0), $color_2 = array(0, 0, 0), $weight = 0.5)
    {
        $f = function($x) use ($weight) { return $weight * $x; };
        $g = function($x) use ($weight) { return (1 - $weight) * $x; };
        $h = function($x, $y) { return round($x + $y); };
        return array_map($h, array_map($f, $color_1), array_map($g, $color_2));
    }
    function colorHex2Rgb($hex = "#000000")
    {
        $f = function($x) { return hexdec($x); };
        return array_map($f, str_split(str_replace("#", "", $hex), 2));
    }
    function colorRgb2Hex($rgb = array(0, 0, 0))
    {
        $f = function($x) { return str_pad(dechex($x), 2, "0", STR_PAD_LEFT); };
        return "#" . implode("", array_map($f, $rgb));
    }
    function colorShade($color, $weight = 0.5)
    {
        $t = $color;
        if(is_string($color)) $t = $this->colorHex2Rgb($color);
        $u = $this->colorMix($t, array(255, 255, 255), $weight);
        if(is_string($color)) return $this->colorRgb2Hex($u);
        return $u;
    }
    
    function downloadDesktopExcel($excelFileDetail, $sheetArray, $output='xlsx', $download='yes'){           

        $fileName=strtolower(str_replace(" ","-",$excelFileDetail['title']))."-".time();
        $excelCreateObj = \Excel::create($fileName, function($excel) use($excelFileDetail, $sheetArray) {
            $excel->setTitle($excelFileDetail['title']);
            foreach ($sheetArray as $sheetDetail) {
                $excel->sheet($sheetDetail['otherParams']['sheetName'], function($sheet) use($sheetDetail) {
                    //set header row and its format
                    $sheet->row(1, $sheetDetail['labelArray']);
                    $sheet->row(1, function($row){
                        $row->setBackground("#CCCCCC");
                        $row->setFontColor('#000000');
                        $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(10);
                    });
                    ////////////
                    if(count($sheetDetail['columnFormat'])>0){
                        $sheet->setColumnFormat($sheetDetail['columnFormat']);
                    }

                    ///Set details rows
                    $rowNo = 2;
                    for($i=0;$i<count($sheetDetail['dataArray']);$i++) {
                        $sheet->row($rowNo, $sheetDetail['dataArray'][$i]);
                        //print_r('expression');exit;
                        if(isset($sheetDetail['cellBackgroundArray'])) {
                            $column = 'A';
                            for($j=0;$j<count($sheetDetail['dataArray'][$i]);$j++) {
                                $background=$sheetDetail['cellBackgroundArray'][$i][$j];
                                if($background!="") {
                                    $sheet->cell($column.$rowNo, function($cell) use($background) {
                                        $cell->setBackground(strtoupper($background));
                                    });
                                }
                                $column++;
                            }
                        }
                        $column = 'A';
                        if(isset($sheetDetail['columnsToAlign'])) {
                            for($j=0;$j<count($sheetDetail['dataArray'][$i]);$j++) {
                                if(array_key_exists($column, $sheetDetail['columnsToAlign'])){
                                    $align = $sheetDetail['columnsToAlign'][$column];
                                    $sheet->cell($column.$rowNo, function($cell) use($align){
                                            $cell->setAlignment($align);
                                        });
                                }
                                        
                                $column++;
                            }
                        }
                        $rowNo++;
                    }
                    //////////

                    //////////Set Summary Row and its formatting////
                    if (isset($sheetDetail['summaryRow'])) {
                        $sheet->row($rowNo++, []);
                        $sheet->row($rowNo, $sheetDetail['summaryRow']);
                        $sheet->row($rowNo, function($row){
                            $row->setFontColor('#000000');
                            $row->setFontWeight('bold');
                            $row->setFontFamily('Arial');
                            $row->setFontSize(10);
                        });
                    }
                    //////////////////

                    $rowNo++;
                    $rowNo++;
                    $rowNo++;

                    ////Code to add charts for which information is passed
                    foreach ($sheetDetail['charts'] as $chart) {
                        try{
                            $dataseriesLabels = array(
                                new \PHPExcel_Chart_DataSeriesValues('String', NULL, NULL, count($chart['dataseriesLabels']), $chart['dataseriesLabels'], NULL),
                            );
                            $xAxisTickValues = array(
                                new \PHPExcel_Chart_DataSeriesValues('String', NULL, NULL, count($chart['xAxisTickValues']), $chart['xAxisTickValues'], NULL),
                                
                            );
                            $dataSeriesValues = array(
                                new \PHPExcel_Chart_DataSeriesValues('Number', NULL, NULL, count($chart['dataSeriesValues']), $chart['dataSeriesValues'], NULL),
                            );
                            $ds=new \PHPExcel_Chart_DataSeries(
                                \PHPExcel_Chart_DataSeries::TYPE_PIECHART,
                                NULL,
                                range(0, count($dataSeriesValues)-1),
                                $dataseriesLabels,
                                $xAxisTickValues,
                                $dataSeriesValues
                            );
                            // Set layout
                            //  Set up a layout object for the Pie chart
                            $layout = new \PHPExcel_Chart_Layout();
                            //$layout->setShowLegendKey(TRUE)->setShowPercent(TRUE)->setShowCatName(TRUE);
                            $layout->setShowLegendKey(TRUE)->setShowPercent(TRUE);
                           

                            $pa=new \PHPExcel_Chart_PlotArea($layout, array($ds));
                            $legend=new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, $layout, TRUE);
                            $title=new \PHPExcel_Chart_Title($chart['title']);
                            $xaxistitle=new \PHPExcel_Chart_Title($chart['xAxisTickValues']);

                            $chart= new \PHPExcel_Chart(
                                                'chart1',
                                                $title,
                                                $legend,
                                                $pa,
                                                true,
                                                0,
                                                NULL, 
                                                NULL
                                                );

                            $chart->setTopLeftPosition('A'.$rowNo);
                            $chart->setBottomRightPosition('D'.($rowNo+10));
                            $sheet->addChart($chart);
                            $rowNo = $rowNo + 11;
                        }
                        catch(Exception $e){
                            \Log::info($e->getMessage());

                        }
                    }                    
                    ////////////////////////

                    if (isset($sheetDetail['autofilter']) && $sheetDetail['autofilter'] == 'no') {
                        //if auto filter is set to no we simply ignore to start auto filter setting
                    }
                    else{                        
                        $sheet->setAutoFilter();                        
                    }
                    if(isset($sheetDetail['otherParams']['freezePane'])) {
                        $sheet->setFreeze($sheetDetail['otherParams']['freezePane']);
                    }

                    ///This is to auto resize column size
                    foreach(range('A',$this->calculateColumns($sheetDetail['labelArray'])) as $columnID) {
                        \Log::info('columnID'.$columnID);
                        $calculatedWidth = $sheet->getColumnDimension($columnID)->getWidth();
                        \Log::info('calculatedWidth'.$calculatedWidth);
                        $sheet->setWidth($columnID,$this->calculateCustomWidth($columnID,$sheetDetail['dataArray'],$sheetDetail['labelArray'])+1);
                    }
                    ///////////////////////////////////////////////////////////////////////////////////
                    

                    
                });
            }
        });

        if($download == 'yes'){
            $excelCreateObj->export($output);
        }else{
            $excelCreateObj->store($output);
        }
        $exportFile=storage_path('exports').'/'.$fileName.'.xlsx';
        return $exportFile;
    }

    function calculateColumns($labelArray){
        $column = 'A';
        foreach ($labelArray as $value) {
            $column++;
        }
        return --$column;
    }

    function calculateCustomWidth($columnID,$dataArray,$labelArray){
        $maxLength = 0;
        $labelLength = 0;
        for($i=0;$i<count($dataArray);$i++) {
            $row = $dataArray[$i];            
            $column = 'A';
            foreach ($labelArray as $value) {
                if ($column == $columnID) {
                    $labelLength =  strlen($value);
                    $labelLength+=2;//because label is in bold
                }
                $column++;
            }
            $column = 'A';
            foreach ($dataArray[$i] as $value) {                    
                if ($column == $columnID) {
                    $length = strlen($value);
                    if($maxLength < $length){
                        $maxLength = $length;
                    }
                }                
                $column++;
            }
        }
        if ($maxLength < $labelLength) {
            $maxLength = $labelLength;
        }
        \Log::info("width for column ".$columnID." = ".$maxLength);
        return $maxLength;
    }

    function calcTaxYear(){
        //$today = Carbon::today();
        $currentyear = date_format(Carbon::now(),"Y");
        $newTaxYearDate = Carbon::parse('06-04-'.$currentyear);//new tax year
        if(Carbon::parse($newTaxYearDate)->gt(Carbon::now())){
            return ($currentyear-1).'-'.$currentyear;
        }
        else{
            return $currentyear.'-'.($currentyear+1);
        }
    }

    function generateReport($reportYear){
        $lableArray = [
                    'Full Name',
                    'Type of Vehicle',
                    'Type of Fuel used',
                    'Vehicle Index',
                    'Make',
                    'Model',
                    'Date first registered',
                    'CO2',
                    'C02 %',
                    'Engine Size (Cubic Capacity )',
                    'Date vehicle was available from',
                    'Date vehicle was no longer available',
                    'Vehicle List Price (Non-commercial) / Benefit charge (Commercial)',
                    'FULL Benefit / Cash Eqiv',
                    'No. Days in tax year',
                    'Prorat\'d BIK Based on no. of days (£)',
                    'Private use days in tax year',
                    'Fuel card used during tax year',
                    'Fuel Benefit Charge (£)',
                    'Prorat\'d Fuel Benefit Based on no. of days (£)',
                ];
                
                $dataArray = [];
                //$taxYearRange = explode('-', $this->calcTaxYear());
                $taxYearRange = explode('-', $reportYear);
                $taxYearStartDate = Carbon::parse('06-04-'.$taxYearRange[0]);//date($taxYearRange[0].'-04-06');
                $taxYearEndDate = Carbon::parse('05-04-'.$taxYearRange[1]);//date($taxYearRange[1].'-04-05');
                $vehicle_usage_history1 = VehicleUsageHistory::with('vehicle_history')->with('user')->with('vehicle_history.type')
                                 ->whereBetween('from_date', [$taxYearStartDate, $taxYearEndDate])
                                 ->orderBy('id','DESC')->get();
                $vehicle_usage_history2 = VehicleUsageHistory::with('vehicle_history')->with('user')
                                         ->whereBetween('to_date', [$taxYearStartDate, $taxYearEndDate])
                                         ->orderBy('id','DESC')->get();
                $vehicle_usage_history = $vehicle_usage_history1->merge($vehicle_usage_history2);
                //$vehicle_usage_history = $vehicle_usage_history->unique('vehicle_id');
                foreach ($vehicle_usage_history as $key => $value) {
                    $fuelBenefitCash = 0;
                    $fuelBenefitCharge = 0;
                    $usageStart = Carbon::parse($taxYearStartDate)->gt(Carbon::parse($value->from_date))? $taxYearStartDate : Carbon::parse($value->from_date);
                    $usageEnd = $value->to_date == null ? Carbon::now() : (Carbon::parse($taxYearEndDate)->gt(Carbon::parse($value->to_date))? Carbon::parse($value->to_date) : $taxYearEndDate) ;
                    $vehicleUsage = $value->vehicle_history->usage_type ? $value->vehicle_history->usage_type : ($value->vehicle_history->type->usage_type ? $value->vehicle_history->type->usage_type : '');
                    //$vehicleUsedDays = $usageEnd->diffForHumans($usageStart);
                    $vehicleUsedDays = $usageEnd->diff($usageStart)->days;
                    if ($vehicleUsage == 'Commercial') {
                        $fuel_benefit_commercial = Settings::where('key','fuel_benefit_commercial')->first();
                        $fuelBenefitCash = $fuel_benefit_commercial->value;
                    }
                    else{
                        $fuelBenefitCash = $value->vehicle_history->P11D_list_price * $value->vehicle_history->type->hmrc_co2;
                    }
                    if ($vehicleUsage == 'Commercial') {
                        $fuel_benefit_commercial = Settings::where('key','fuel_benefit_commercial')->first();
                        $fuelBenefitCharge = $fuel_benefit_commercial->value;

                    }
                    else{
                        $fuel_benefit_noncommercial = Settings::where('key','fuel_benefit_noncommercial')->first();
                        $fuelBenefitCharge = $fuel_benefit_noncommercial->value;

                    }
                    $data = [

                        $value->user->first_name.' '.$value->user->last_name,
                        $value->vehicle_history->type->vehicle_type,
                        $value->vehicle_history->type->fuel_type,
                        $value->vehicle_history->registration,
                        $value->vehicle_history->type->manufacturer,
                        $value->vehicle_history->type->model,
                        $value->vehicle_history->dt_registration,
                        $value->vehicle_history->CO2 ? $value->vehicle_history->CO2 : ($value->vehicle_history->type->CO2 ? $value->vehicle_history->type->CO2 : 0),
                        $value->vehicle_history->type->hmrc_co2 ? $value->vehicle_history->type->hmrc_co2 : 0,
                        $value->vehicle_history->type->engine_size,
                        Carbon::parse($usageStart)->format('d M Y'),
                        Carbon::parse($usageEnd)->format('d M Y'),
                        $value->vehicle_history->P11D_list_price,
                        $fuelBenefitCash,
                        $vehicleUsedDays,
                        number_format((float)(($fuelBenefitCash/365)*$vehicleUsedDays), 2, '.', ''),
                        $value->vehicle_history->calcPrivateUseDays(),
                        $value->user->fuel_card_issued == 1 ? 'Yes' : 'No',
                        $fuelBenefitCharge,
                        number_format((float)(($fuelBenefitCharge/365)*$vehicleUsedDays), 2, '.', ''),
                    ];
                    array_push($dataArray, $data);
                }

                $excelFileDetail=array(
                    'title' => "P11D Benefits in Kind ".$reportYear,
                    );

                $sheetArray=array();
                $sheet=array();
                $sheet['otherParams'] = [
                    'sheetName' => "P11D Report"
                ];
                $sheet['labelArray'] = $lableArray;
                $sheet['dataArray'] = $dataArray;
                $sheet['columnFormat'] = [];
                $sheet['charts'] = [];
                $sheet['summaryRow'] = [];
                array_push($sheetArray, $sheet);
                $commonHelper = new Common();
                $reportFile = $commonHelper->downloadDesktopExcel($excelFileDetail,$sheetArray,'xlsx','no');
                return $reportFile;
    }
}