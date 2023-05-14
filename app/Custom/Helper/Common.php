<?php
namespace App\Custom\Helper;
use AdamWathan\Form\Elements\Date;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\VehiclesController;
use Faker\Provider\DateTime;
use Mail;
use Exception;
use Carbon\Carbon;
use App\Models\Settings;

class Common {

    function convertBstToUtc($dateParam){
        return Carbon::createFromFormat('d/m/Y H:i:s', $dateParam,  config('config-variables.format.displayTimezone'))->setTimezone('UTC');
        // return Carbon::parse($dateParam, config('config-variables.format.displayTimezone'))->setTimezone('UTC');
    }

    function convertBstToUtcWithParse($dateParam){
        return Carbon::parse($dateParam, config('config-variables.format.displayTimezone'))->setTimezone('UTC');
    }

    function toExcel($excelFileDetail, $sheetArray, $output='xlsx', $download='yes'){
        $fileName=strtolower(str_replace(" ","-",$excelFileDetail['title']))."-".time();
        $excelCreateObj = \Excel::create($fileName, function($excel) use($excelFileDetail, $sheetArray) {
            $excel->setTitle($excelFileDetail['title']);
            foreach ($sheetArray as $sheetDetail) {
                $excel->sheet($sheetDetail['otherParams']['sheetName'], function($sheet) use($sheetDetail) {
                    $sheet->row(1, $sheetDetail['labelArray']);
                    $sheet->row(1, function($row){
                        // $row->setBackground("#FFFFFF");
                        $row->setFontColor('#000000');
                        $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(10);
                    });
                    if(count($sheetDetail['columnFormat'])>0){
                        $sheet->setColumnFormat($sheetDetail['columnFormat']);
                    }
                    if (isset($sheetDetail['columnColor']))
                    {
                        if(count($sheetDetail['columnColor'])>0){
                            foreach ($sheetDetail['columnColor'] as $columnId => $columnColor) {
                                $sheet->getStyle($columnId)->applyFromArray(array(
                                    'fill' => array(
                                        'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => $columnColor)
                                    )
                                ));
                            }
                        }
                    }
                    $rowNo = 2;
                    for($i=0;$i<count($sheetDetail['dataArray']);$i++) {
                        $sheet->row($rowNo, $sheetDetail['dataArray'][$i]);
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
    
    function downloadDesktopExcel($excelFileDetail, $sheetArray, $output='xlsx', $download='yes', $filenameTimestamp='yes',$fileNameLowerCase='yes'){           
        if($fileNameLowerCase=='yes'){
            $fileName=strtolower(str_replace(" ","-",$excelFileDetail['title']));
        }else{
            $fileName=$excelFileDetail['title'];
        }
        if($filenameTimestamp == 'yes')
            $fileName=strtolower(str_replace(" ","-",$excelFileDetail['title']))."-".time();
        $excelCreateObj = \Excel::create($fileName, function($excel) use($excelFileDetail, $sheetArray) {
            $excel->setTitle($excelFileDetail['title']);
            foreach ($sheetArray as $sheetDetail) {
                $excel->sheet($sheetDetail['otherParams']['sheetName'], function($sheet) use($sheetDetail) {
                    //set header row and its format
                    $headingStartRow=1; //heading row start
                    if(isset($sheetDetail['headingLabelArray']) && !empty($sheetDetail['headingLabelArray'])){
                        $sheet->row($headingStartRow, $sheetDetail['headingLabelArray']);
                        $headingStartRow+=1;
                    }
                    $sheet->row($headingStartRow, $sheetDetail['labelArray']);
                    $sheet->row($headingStartRow, function($row){
                        $row->setBackground("#CCCCCC");
                        $row->setFontColor('#000000');
                        $row->setFontWeight('bold');
                        $row->setFontFamily('Arial');
                        $row->setFontSize(10);
                    });
                   
                    if(count($sheetDetail['columnFormat'])>0){
                        $sheet->setColumnFormat($sheetDetail['columnFormat']);
                    }

                    ///Set details rows
                    $rowNo = $headingStartRow+1; //value input start after the heading row that's why added $headingStartRow+1.
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
                    if(isset($sheetDetail['charts'])) {
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
                    }                    
                    ////////////////////////

                    if (isset($sheetDetail['autofilter']) && $sheetDetail['autofilter'] == 'no') {
                        //if auto filter is set to no we simply ignore to start auto filter setting
                    }
                    else{   
                        if(isset($sheetDetail['headingLabelArray']) && !empty($sheetDetail['headingLabelArray'])){
                            if(isset($sheetDetail['headingLabelArrayFilterRange']) && !empty($sheetDetail['headingLabelArrayFilterRange'])){
                                $sheet->setAutoFilter($sheetDetail['headingLabelArrayFilterRange']);
                            }
                        }else{                     
                            $sheet->setAutoFilter();
                        }                        
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
        if($maxLength == 0) {
            $maxLength = 15;
        }
        \Log::info("width for column ".$columnID." = ".$maxLength);
        return $maxLength;
    }

    function calcMonthlyCurrentData_oldBackup($costs_json, $selectedDate, $vehicleId, $vehicleArchiveHistory,$vehicleDtAddedToFleet=null,$isInsuranceCostOverride=null,$isTelematicsCostOverride=null,$isBasedIncurrentDate=false,$typeofCost = '',$selectedDateValue=null,$fleetCostSettingsData = null)
    {

        $costs_array = json_decode($costs_json, true);
        $currentCost = 0;
        $currentDate = '';
        $currentDateValue = '';
        $currentMonthData = [];
        $fromDate = '';
        $toDate = '';
        $vehicleToDate = '';
        $current = '';
        $currentMonthDates = [];
        $totalDaysCount = '';
        $selectedDateValue = Carbon::parse($selectedDate)->startOfMonth();
        $vehicleHistoryEventDate = Carbon::parse($vehicleArchiveHistory['event_date_time']);
        $vehicleDtAddedToFleet = $vehicleDtAddedToFleet ? Carbon::parse($vehicleDtAddedToFleet) : null;
        $vehicleDtAddedToFleetMonth = $vehicleDtAddedToFleet ? Carbon::parse($vehicleDtAddedToFleet)->format('m') : null;
        $vehicleDtAddedToFleetYear = $vehicleDtAddedToFleet ? Carbon::parse($vehicleDtAddedToFleet)->format('Y') : null;
        $vehicleDtAddedToFleetStartOfMonth = Carbon::parse($vehicleDtAddedToFleet)->startOfMonth();
        if($selectedDateValue->gt($vehicleHistoryEventDate) && $vehicleArchiveHistory['event'] == "Archived") {
            $currentMonthData['currentCost'] = 0;
            $currentMonthData['currentDate'] = '';
            $currentMonthData['currentDateValue'] = 0;
            $currentMonthData['totalDaysCount'] = 0;
            $currentMonthData['currentMonthDates'] = '';
            return $currentMonthData;
        }

        $fleetCostSettingsData = $fleetCostSettingsData == null ? Settings::where('key', 'fleet_cost_area_detail')->first() : $fleetCostSettingsData ;
        //$fleetCostSettingsData = Settings::where('key', 'fleet_cost_area_detail')->first();
        $fleetCostDataJson = $fleetCostSettingsData->value;
        $fleetCostData = json_decode($fleetCostDataJson, true);
        if(isset($costs_array)) {
            foreach ($costs_array as $key=>$cost) {
                $fromDate = Carbon::parse($cost['cost_from_date'])->format('d M Y');
                $toDate =  $cost['cost_to_date'] !="" ? Carbon::parse($cost['cost_to_date'])->format('d M Y') : Carbon::parse($selectedDate)->endOfMonth();

                $current = Carbon::now()->startOfDay();
                $currentYear = Carbon::now()->startOfDay()->format('Y');
                $currentMonth = Carbon::now()->startOfDay()->format('m');
                $selectedDateValue = Carbon::parse($selectedDate)->startOfMonth();
                $selectedDateValueEnd = Carbon::parse($selectedDate)->endOfMonth()->startOfDay();
                $selectedMonth = Carbon::parse($selectedDate)->format('m');
                $selectedYear = Carbon::parse($selectedDate)->format('Y');
                $costFromMonth = Carbon::parse($cost['cost_from_date'])->format('m');
                $costFromYear = Carbon::parse($cost['cost_from_date'])->format('Y');
                $costFromDate = Carbon::parse($cost['cost_from_date']);
                $costToDate = $cost['cost_to_date'] !="" ?Carbon::parse($cost['cost_to_date']) : Carbon::parse($selectedDate)->endOfMonth() ;

                $costToMonth = $cost['cost_to_date'] != "" ? Carbon::parse($cost['cost_to_date'])->format('m') : '';
                $costToYear = $cost['cost_to_date'] != "" ? Carbon::parse($cost['cost_to_date'])->format('Y') : '';
                $eventDifferenceDays = '';
                $vehicleHistoryEventDate = Carbon::parse($vehicleArchiveHistory['event_date_time']);
                $vehicleHistoryEventStartDate = Carbon::parse($vehicleHistoryEventDate)->startOfMonth();
                $vehicleHistoryEventEndDate = Carbon::parse($vehicleHistoryEventDate)->endOfMonth()->startOfDay();

                $vehicleToDate = null;
                if($selectedDateValue->gte($costFromDate)) {
                    if($cost['cost_continuous'] == 'true' || $selectedDateValueEnd->lte($costToDate)) {
                        $vehicleToDate = $selectedDateValueEnd;
                    } else if($selectedDateValueEnd->gt($costToDate) && $selectedDateValue->lte($costToDate)) {
                        $vehicleToDate = $costToDate;
                    }
                } else if($costFromDate->gt($selectedDateValue)) {
                    $vehicleToDate = $costToDate;
                }


                if (($costFromMonth == $selectedMonth && $costFromYear == $selectedYear) || ($costToMonth == $selectedMonth && $costFromYear == $selectedYear) ) {
                    $dateRange = [
                        'start_date' => $fromDate,
                        'end_date' => $toDate
                    ];
                    array_push($currentMonthDates,$dateRange);
                }

                if($selectedDateValue->gte($vehicleHistoryEventStartDate) && $selectedDateValueEnd->lte($vehicleHistoryEventEndDate)){
                    $vehicleHistoryEventDays = $vehicleHistoryEventDate->diffInDays($vehicleHistoryEventStartDate);
                    $eventDifferenceDays = $vehicleHistoryEventDays + 1;

                    if($vehicleHistoryEventDate->gte($costToDate)) {
                        $eventDateDiff = $vehicleHistoryEventDate->diffInDays($costToDate);
                        $eventDifferenceDays = $eventDifferenceDays - $eventDateDiff;
                    }

                    if($costFromDate->gt($vehicleHistoryEventStartDate)) {
                        $eventDateDiff = $costFromDate->diffInDays($vehicleHistoryEventStartDate);
                        $eventDifferenceDays = $eventDifferenceDays - $eventDateDiff;
                    }
                    $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);

                } else {
                    $diffDays = $costToDate->diffInDays($costFromDate);
                    $eventDifferenceDays = $diffDays + 1;
                    $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);
                }
                $diffDays = $costToDate->diffInDays($costFromDate);

                // DateAddedToFleet Days Count
                $dtFleetDiffDaysCount = 0;
                $toCalculateDateFlag = true;
                

                if($vehicleToDate && $vehicleDtAddedToFleet && $vehicleToDate->gte($vehicleDtAddedToFleet)) {
                    $vehicleDtAddedToFleetToTake = $vehicleDtAddedToFleet;
                    if($vehicleDtAddedToFleet->lt($selectedDateValue)) {
                        $vehicleDtAddedToFleetToTake = $selectedDateValue;
                    }

                    if($vehicleId == $vehicleArchiveHistory['vehicle_id'] && $vehicleArchiveHistory['event'] == 'Archived'){
                        if($vehicleHistoryEventDate->lt($vehicleDtAddedToFleetToTake)) {
                            $dtFleetDiffDaysCount = 0;
                            $toCalculateDateFlag = false;
                        } else if($vehicleHistoryEventDate->lt($vehicleToDate)) {
                            $vehicleToDate = $vehicleHistoryEventDate;
                        }
                    }
                    if($toCalculateDateFlag) {
                        $dtFleetDiffDays = Carbon::parse($vehicleToDate)->diffInDays(Carbon::parse($vehicleDtAddedToFleetToTake));
                        $dtFleetDiffDaysCount = $dtFleetDiffDays+1;
                    }
                }

                $vehicleAddedToFleetObj = new \DateTime($vehicleDtAddedToFleet);
                $vehicleFromDateObj = new \DateTime($fromDate);
                $vehicleToDateObj = new \DateTime($toDate);

                if($isBasedIncurrentDate){
                    if($current->gte($costFromDate) && $current->lte($costToDate) && $cost['cost_to_date'] != ''){
                        $costValue = str_replace(',', '', $cost['cost_value']);
                        $currentCost = $currentCost + $costValue;
                    } else if(isset($cost['cost_continuous']) && $cost['cost_continuous'] == 'true' && $current->gte($costFromDate)) {
                            $costValue = str_replace(',', '', $cost['cost_value']);
                            $currentCost = $currentCost + $costValue;
                        }
                } else{
                    if($selectedDateValue->gt($costFromDate) && $selectedDateValueEnd->lt($costToDate)){
                        $costValue = str_replace(',', '', $cost['cost_value']);
                        $daysInCurMonth = Carbon::parse($selectedDate)->daysInMonth;
                        $totalCost = str_replace(',', '', $cost['cost_value']);
                        $currentDate = Carbon::parse($cost['cost_from_date'])->format('m-Y');
                        $firstCostDate = Carbon::parse($cost['cost_from_date']);
                        $endOfMonthDate = Carbon::parse($cost['cost_to_date']);
                        $currentFromDaysCount = $firstCostDate->diffInDays($endOfMonthDate);
                        $currentValueCount = $currentFromDaysCount + 1;
                        
                        if($vehicleId == $vehicleArchiveHistory['vehicle_id'] && $vehicleArchiveHistory['event'] == 'Archived'){
                                if($selectedDateValue->gte($vehicleHistoryEventStartDate) && $selectedDateValueEnd->lte($vehicleHistoryEventEndDate)) {
                                    $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$eventDifferenceDays;
                                } else {
                                    $currentCost = 0;
                                }
                                if($selectedDateValue->gte($vehicleDtAddedToFleetStartOfMonth) || is_null($vehicleDtAddedToFleet)){
                                    if(($vehicleAddedToFleetObj >= $vehicleFromDateObj && $vehicleAddedToFleetObj <= $vehicleToDateObj && $selectedMonth == $vehicleDtAddedToFleetMonth) && $selectedYear == $vehicleDtAddedToFleetYear) {
                                        if($vehicleDtAddedToFleet && (isset($fleetCostData['annual_insurance_cost']) && $isInsuranceCostOverride != 1 
                                            || isset($fleetCostData['telematics_insurance_cost']) && $isTelematicsCostOverride != 1 || isset($cost['json_type']) == 'monthlyVehicleTax')) {

                                            $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$dtFleetDiffDaysCount;
                                        } else {
                                            $currentCost = 0;
                                        }
                                    }
                                } else {
                                    $currentCost = 0;
                                }
                        }else {
                            if($selectedDateValue->gte($vehicleDtAddedToFleetStartOfMonth) || is_null($vehicleDtAddedToFleet)){
                                if(($vehicleAddedToFleetObj >= $vehicleFromDateObj && $vehicleAddedToFleetObj <= $vehicleToDateObj && $selectedMonth == $vehicleDtAddedToFleetMonth) && $selectedYear == $vehicleDtAddedToFleetYear) {
                                    if($vehicleDtAddedToFleet && (isset($fleetCostData['annual_insurance_cost']) && $isInsuranceCostOverride != 1 
                                        || isset($fleetCostData['telematics_insurance_cost']) && $isTelematicsCostOverride != 1 || isset($cost['json_type']) == 'monthlyVehicleTax')) {
                                        $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$dtFleetDiffDaysCount;
                                    } else {
                                        $currentCost = 0;
                                    }
                                } else {
                                    $currentCost = $costValue;
                                }
                            } else {
                                $currentCost = 0;
                            }
                        }
                        $currentDateValue = Carbon::parse($cost['cost_from_date'])->format('d M Y');
                        $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);
                    }else{
                        if(($selectedMonth == $costFromMonth && $selectedMonth == $costToMonth) && $selectedYear == $costFromYear && $selectedYear == $costToYear){
                            $currentDate = Carbon::parse($cost['cost_from_date'])->format('m-Y');
                            $currentDateValue = Carbon::parse($cost['cost_from_date'])->format('d M Y');
                            $differenceDays = $diffDays + 1;
                            $costValue = str_replace(',', '', $cost['cost_value']);
                            $daysInCurMonth = Carbon::parse($selectedDate)->daysInMonth;

                            $endOfMonthDate = Carbon::parse($cost['cost_to_date']);
                            $firstCostDate = Carbon::parse($cost['cost_from_date']);
                            $daysCount = $firstCostDate->diffInDays($endOfMonthDate);
                            $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate); 

                            $currentFromDaysCount = $firstCostDate->diffInDays($endOfMonthDate);
                            $currentValueCount = $currentFromDaysCount + 1;
                            if($vehicleId = $vehicleArchiveHistory['vehicle_id'] && $vehicleArchiveHistory['event'] == 'Archived'){
                                    if($selectedDateValue != $vehicleHistoryEventStartDate || $selectedDateValueEnd != $vehicleHistoryEventEndDate){
                                        $currentCost = 0;
                                    }

                                    if($selectedDateValue->gte($vehicleDtAddedToFleetStartOfMonth) || is_null($vehicleDtAddedToFleet)){
                                        if(($vehicleAddedToFleetObj >= $vehicleFromDateObj && $vehicleAddedToFleetObj <= $vehicleToDateObj && $selectedMonth == $vehicleDtAddedToFleetMonth) && $selectedYear == $vehicleDtAddedToFleetYear) {
                                            if($vehicleDtAddedToFleet && (isset($fleetCostData['annual_insurance_cost']) && $isInsuranceCostOverride != 1 
                                                || isset($fleetCostData['telematics_insurance_cost']) && $isTelematicsCostOverride != 1 || isset($cost['json_type']) == 'monthlyVehicleTax')) {
                                                $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$dtFleetDiffDaysCount;
                                            } else {
                                                $currentCost = 0;
                                            }
                                        }
                                    } else {
                                        $currentCost = 0;
                                    } 
                                
                            } else {
                                if($selectedDateValue->gte($vehicleDtAddedToFleetStartOfMonth) || is_null($vehicleDtAddedToFleet)){
                                    if(($vehicleAddedToFleetObj >= $vehicleFromDateObj && $vehicleAddedToFleetObj <= $vehicleToDateObj && $selectedMonth == $vehicleDtAddedToFleetMonth) && $selectedYear == $vehicleDtAddedToFleetYear) {
                                        if($vehicleDtAddedToFleet && (isset($fleetCostData['annual_insurance_cost']) && $isInsuranceCostOverride != 1 
                                            || isset($fleetCostData['telematics_insurance_cost']) && $isTelematicsCostOverride != 1 || isset($cost['json_type']) == 'monthlyVehicleTax')) {
                                            $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$dtFleetDiffDaysCount;
                                        } else {
                                            $currentCost = 0;
                                        }
                                    } else {
                                        $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$currentValueCount;
                                    }
                                } else {
                                    $currentCost = 0;
                                }
                            }
                        }
                        else{
                            if($selectedMonth == $costFromMonth && $selectedYear == $costFromYear){
                                $endOfMonthDate = Carbon::parse($cost['cost_from_date'])->endOfMonth();
                                $daysInCurMonth = Carbon::parse($selectedDate)->daysInMonth;
                                $costValue = str_replace(',', '', $cost['cost_value']);
                                $firstCostDate = Carbon::parse($cost['cost_from_date']);
                                $currentFromDaysCount = $firstCostDate->diffInDays($endOfMonthDate);
                                $currentValueCount = $currentFromDaysCount + 1;
                                $diffDaysCounts = $diffDays + 1;

                                $endOfMonthDate = Carbon::parse($cost['cost_from_date'])->endOfMonth();
                                $firstCostDate = Carbon::parse($cost['cost_from_date']);
                                $daysCount = $firstCostDate->diffInDays($endOfMonthDate);
                                $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);
                                if($vehicleId = $vehicleArchiveHistory['vehicle_id'] && $vehicleArchiveHistory['event'] == 'Archived'){
                                    if($selectedDateValue != $vehicleHistoryEventStartDate || $selectedDateValueEnd != $vehicleHistoryEventEndDate){
                                        $currentCost = 0;
                                    }

                                    if($selectedDateValue->gte($vehicleDtAddedToFleetStartOfMonth) || is_null($vehicleDtAddedToFleet)){
                                        if(($vehicleAddedToFleetObj >= $vehicleFromDateObj && $vehicleAddedToFleetObj <= $vehicleToDateObj && $selectedMonth == $vehicleDtAddedToFleetMonth) && $selectedYear == $vehicleDtAddedToFleetYear) {
                                            if($vehicleDtAddedToFleet && (isset($fleetCostData['annual_insurance_cost']) && $isInsuranceCostOverride != 1 
                                                || isset($fleetCostData['telematics_insurance_cost']) && $isTelematicsCostOverride != 1 || isset($cost['json_type']) == 'monthlyVehicleTax')) {
                                                $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$dtFleetDiffDaysCount;
                                            } else {
                                                $currentCost = 0;
                                            }
                                        }
                                    } else {
                                        $currentCost = 0;
                                    } 
                                
                                } else {
                                    if($selectedDateValue->gte($vehicleDtAddedToFleetStartOfMonth) || is_null($vehicleDtAddedToFleet)){
                                        if(($vehicleAddedToFleetObj >= $vehicleFromDateObj && $vehicleAddedToFleetObj <= $vehicleToDateObj && $selectedMonth == $vehicleDtAddedToFleetMonth) && $selectedYear == $vehicleDtAddedToFleetYear) {
                                            if($vehicleDtAddedToFleet && (isset($fleetCostData['annual_insurance_cost']) && $isInsuranceCostOverride != 1 
                                                || isset($fleetCostData['telematics_insurance_cost']) && $isTelematicsCostOverride != 1 || isset($cost['json_type']) == 'monthlyVehicleTax')) {
                                                $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$dtFleetDiffDaysCount;
                                            } else {
                                                $currentCost = 0;
                                            }
                                        } else {
                                            $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$currentValueCount;
                                        }
                                    } else {
                                        $currentCost = 0;
                                    }
                                }

                                $currentDate = Carbon::parse($cost['cost_from_date'])->format('m-Y');
                                $currentDateValue = Carbon::parse($cost['cost_from_date'])->format('d M Y');
                            } else if($selectedMonth == $costToMonth && $selectedYear == $costToYear){
                                $startOfMonthDate = Carbon::parse($cost['cost_to_date'])->startOfMonth();
                                $daysInCurMonth = Carbon::parse($selectedDate)->daysInMonth;
                                $costValue = str_replace(',', '', $cost['cost_value']);
                                $lastCostDate = Carbon::parse($cost['cost_to_date']);
                                $currentToDaysCount = $lastCostDate->diffInDays($startOfMonthDate);
                                $currentValueCount = $currentToDaysCount + 1;
                                $diffDaysCounts = $diffDays + 1;

                                $endOfMonthDate = Carbon::parse($cost['cost_to_date'])->startOfMonth();
                                $firstCostDate = Carbon::parse($cost['cost_from_date']);
                                $daysCount = $firstCostDate->diffInDays($endOfMonthDate);
                                $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);

                                if($vehicleId = $vehicleArchiveHistory['vehicle_id'] && $vehicleArchiveHistory['event'] == 'Archived'){
                                    if($selectedDateValue != $vehicleHistoryEventStartDate || $selectedDateValueEnd != $vehicleHistoryEventEndDate){
                                        $currentCost = 0;
                                    }

                                    if($selectedDateValue->gte($vehicleDtAddedToFleetStartOfMonth) || is_null($vehicleDtAddedToFleet)){
                                        if(($vehicleAddedToFleetObj >= $vehicleFromDateObj && $vehicleAddedToFleetObj <= $vehicleToDateObj && $selectedMonth == $vehicleDtAddedToFleetMonth) && $selectedYear == $vehicleDtAddedToFleetYear) {
                                            if($vehicleDtAddedToFleet && (isset($fleetCostData['annual_insurance_cost']) && $isInsuranceCostOverride != 1 
                                                || isset($fleetCostData['telematics_insurance_cost']) && $isTelematicsCostOverride != 1 || isset($cost['json_type']) == 'monthlyVehicleTax')) {
                                                $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$dtFleetDiffDaysCount;
                                            } else {
                                                $currentCost = 0;
                                            }
                                        }
                                    } else {
                                        $currentCost = 0;
                                    }
                                
                                } else {
                                    if($selectedDateValue->gte($vehicleDtAddedToFleetStartOfMonth) || is_null($vehicleDtAddedToFleet)){
                                        if(($vehicleAddedToFleetObj >= $vehicleFromDateObj && $vehicleAddedToFleetObj <= $vehicleToDateObj && $selectedMonth == $vehicleDtAddedToFleetMonth) && $selectedYear == $vehicleDtAddedToFleetYear) {
                                            if($vehicleDtAddedToFleet && (isset($fleetCostData['annual_insurance_cost']) && $isInsuranceCostOverride != 1 
                                                || isset($fleetCostData['telematics_insurance_cost']) && $isTelematicsCostOverride != 1 || isset($cost['json_type']) == 'monthlyVehicleTax')) {
                                                $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$dtFleetDiffDaysCount;
                                            } else {
                                                $currentCost = 0;
                                            }
                                        } else {
                                            $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$currentValueCount;
                                        }
                                    } else {
                                        $currentCost = 0;
                                    }
                                }
                                
                                $currentDate = Carbon::parse($cost['cost_from_date'])->format('m-Y');
                                $currentDateValue = Carbon::parse($cost['cost_from_date'])->format('d M Y');
                            } else if(isset($cost['cost_continuous']) && $cost['cost_continuous'] == 'true'){
                                if($selectedDateValue->gte(Carbon::parse($cost['cost_from_date']))){
                                    $costValue = str_replace(',', '', $cost['cost_value']);
                                    $currentCost = $currentCost + $costValue;
                                    $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);
                                }
                            }
                        }
                    }
                }
            }
        }
        $currentMonthData['currentCost'] = number_format($currentCost,2,'.','');
        $currentMonthData['currentDate'] = $currentDate;
        $currentMonthData['currentDateValue'] = $currentDateValue;
        $currentMonthData['totalDaysCount'] = $totalDaysCount;
        $currentMonthData['currentMonthDates'] = $currentMonthDates;
        return $currentMonthData;
    }

    function calcCurrentMonthBasedOnPeriod_oldBackup($manual_cost_adjustments, $selectedDate, $vehicleId=null, $vehicleArchiveHistory=null,$typeOfCost='')
    {
        $currentMonthData = [];
        $currentDate = '';
        $currentDateValue = '';
        $currentCost = 0;
        $totalDaysCount = '';
        $totalDaysCountFinal = '';
        $currentMonthDates = [];
        $vehicleHistoryEventDays = '';

        $selectedDateValue = Carbon::parse($selectedDate)->startOfMonth();
        $vehicleHistoryEventDate = Carbon::parse($vehicleArchiveHistory['event_date_time']);

        if($selectedDateValue->gt($vehicleHistoryEventDate) && $vehicleArchiveHistory['event'] == "Archived") {
            $currentMonthData['currentCost'] = 0;
            $currentMonthData['currentDate'] = '';
            $currentMonthData['currentDateValue'] = 0;
            $currentMonthData['totalDaysCount'] = 0;
            $currentMonthData['currentMonthDates'] = '';
            return $currentMonthData;
        }

        foreach ($manual_cost_adjustments as $key => $manual_cost_adjustment) {
            $fromDate = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('d M Y');
            $toDate = Carbon::parse($manual_cost_adjustment['cost_to_date'])->format('d M Y');
            $current = Carbon::now()->format('d M Y');
            $currentYear = Carbon::now()->startOfDay()->format('Y');
            $currentMonth = Carbon::now()->startOfDay()->format('m');
            $selectedDateValue = Carbon::parse($selectedDate)->startOfMonth();
            $selectedDateValueEnd = Carbon::parse($selectedDate)->endOfMonth()->startOfDay();
            $selectedMonth = Carbon::parse($selectedDate)->format('m');
            $selectedYear = Carbon::parse($selectedDate)->format('Y');
            $costFromMonth = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('m');
            $costFromYear = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('Y');    
            $costFromDate = Carbon::parse($manual_cost_adjustment['cost_from_date']);
            $costToDate = Carbon::parse($manual_cost_adjustment['cost_to_date']);
            $costToMonth = Carbon::parse($manual_cost_adjustment['cost_to_date'])->format('m');
            $costToYear = Carbon::parse($manual_cost_adjustment['cost_to_date'])->format('Y');

            if (($costFromMonth == $selectedMonth && $costFromYear == $selectedYear) || ($costToMonth == $selectedMonth && $costToYear == $selectedYear) ) {

                $dateRange = [
                    'start_date' => $fromDate,
                    'end_date' => $toDate
                ];

                array_push($currentMonthDates,$dateRange);
            }
            $eventDifferenceDays = '';
            $vehicleHistoryEventDate = Carbon::parse($vehicleArchiveHistory['event_date_time']);
            $vehicleHistoryEventStartDate = Carbon::parse($vehicleHistoryEventDate)->startOfMonth();
            $vehicleHistoryEventEndDate = Carbon::parse($vehicleHistoryEventDate)->endOfMonth()->startOfDay();

            if($selectedDateValue->gte($vehicleHistoryEventStartDate) && $selectedDateValueEnd->lte($vehicleHistoryEventEndDate)){
                    $vehicleHistoryEventDays = $vehicleHistoryEventDate->diffInDays($vehicleHistoryEventStartDate);
                    $eventDifferenceDays = $vehicleHistoryEventDays + 1;

                if($vehicleHistoryEventDate->gte($costToDate)) {
                    $eventDateDiff = $vehicleHistoryEventDate->diffInDays($costToDate);
                    $eventDifferenceDays = $eventDifferenceDays - $eventDateDiff;
                }

                if($costFromDate->gt($vehicleHistoryEventStartDate)) {
                    $eventDateDiff = $costFromDate->diffInDays($vehicleHistoryEventStartDate);
                    $eventDifferenceDays = $eventDifferenceDays - $eventDateDiff;
                }
                $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);

            } else {
                $diffDays = $costToDate->diffInDays($costFromDate);
                $eventDifferenceDays = $diffDays + 1;
                $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);
            }

            $diffDays = $costToDate->diffInDays($costFromDate);

            if($selectedDateValue->gt($costFromDate) && $selectedDateValueEnd->lt($costToDate)){
                $daysInCurMonth = Carbon::parse($selectedDate)->daysInMonth;
                $currentDate = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('m-Y');
                $costValue = str_replace(',', '', $manual_cost_adjustment['cost_value']);
                $diffDaysCounts = $diffDays + 1;
                $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);

                if($vehicleId == $vehicleArchiveHistory['vehicle_id'] && $vehicleArchiveHistory['event'] == 'Archived' && $selectedDateValue->gte($vehicleHistoryEventStartDate) && $selectedDateValueEnd->lte($vehicleHistoryEventEndDate)){
                    $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$eventDifferenceDays;
                }else {
                     $currentCost = $costValue/$diffDaysCounts*$daysInCurMonth;
                }
                $currentDateValue = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('d M Y');
            }else{
                if(($selectedMonth == $costFromMonth && $selectedMonth == $costToMonth) && $selectedYear == $costFromYear && $selectedYear == $costToYear){
                    $daysInCurMonth = Carbon::parse($selectedDate)->daysInMonth;
                    $leaseCurrentDate = $manual_cost_adjustment['cost_to_date'];
                    $currentDate = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('m-Y');
                    $currentDateValue = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('d M Y');
                    $costValue = str_replace(',', '', $manual_cost_adjustment['cost_value']);

                    $endOfMonthDate = Carbon::parse($manual_cost_adjustment['cost_to_date']);
                    $firstCostDate = Carbon::parse($manual_cost_adjustment['cost_from_date']);
                    $daysCount = $firstCostDate->diffInDays($endOfMonthDate);
                    $diffDaysCounts = $diffDays + 1;
                    $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);;
                    if($vehicleId = $vehicleArchiveHistory['vehicle_id'] && $vehicleArchiveHistory['event'] == 'Archived' && $selectedDateValue->gte($vehicleHistoryEventStartDate) && $selectedDateValueEnd->lte($vehicleHistoryEventEndDate)){
                        $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$eventDifferenceDays;
                    }else {
                        $currentCost = $currentCost + $costValue;
                    }
                }
                else{
                    if($selectedMonth == $costFromMonth && $selectedYear == $costFromYear){
                        $costFromDate = Carbon::parse($manual_cost_adjustment['cost_from_date'])->endOfMonth();
                        $daysInCurMonth = Carbon::parse($selectedDate)->daysInMonth;
                        $costValue = str_replace(',', '', $manual_cost_adjustment['cost_value']);
                        $firstCostDate = Carbon::parse($manual_cost_adjustment['cost_from_date']);
                        $currentFromDaysCount = $firstCostDate->diffInDays($costFromDate);
                        $currentValueCount = $currentFromDaysCount + 1;
                        $diffDaysCounts = $diffDays + 1;

                        $endOfMonthDate = Carbon::parse($manual_cost_adjustment['cost_from_date'])->endOfMonth();
                        $firstCostDate = Carbon::parse($manual_cost_adjustment['cost_from_date']);
                        $daysCount = $firstCostDate->diffInDays($endOfMonthDate);
                        $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);

                        if($vehicleId == $vehicleArchiveHistory['vehicle_id'] && $vehicleArchiveHistory['event'] == 'Archived'){
                            if($selectedDateValue != $vehicleHistoryEventStartDate || $selectedDateValueEnd != $vehicleHistoryEventEndDate){
                                $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$currentValueCount;
                            } else {
                                $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$eventDifferenceDays;
                            }
                        } else {
                            $currentCost = $currentCost + ($costValue/$diffDaysCounts)*$currentValueCount;
                        }
                        
                        $currentDate = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('m-Y');
                        $currentDateValue = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('d M Y');
                    }
                    if($selectedMonth == $costToMonth && $selectedYear == $costToYear){
                        $jsonMonthStartDate = Carbon::parse($manual_cost_adjustment['cost_to_date'])->startOfMonth();
                        $daysInCurMonth = Carbon::parse($selectedDate)->daysInMonth;
                        $costValue = str_replace(',', '', $manual_cost_adjustment['cost_value']);
                        $lastCostDate = Carbon::parse($manual_cost_adjustment['cost_to_date']);
                        $currentToDaysCount = $lastCostDate->diffInDays($jsonMonthStartDate);
                        $currentValueCount = $currentToDaysCount + 1;
                        $diffDaysCounts = $diffDays + 1;

                        $endOfMonthDate = Carbon::parse($manual_cost_adjustment['cost_to_date'])->startOfMonth();
                        $firstCostDate = Carbon::parse($manual_cost_adjustment['cost_from_date']);
                        $daysCount = $firstCostDate->diffInDays($endOfMonthDate);
                        $totalDaysCount = $this->getTotalDaysCount($selectedDate,$costFromDate,$costToDate);;

                        if($vehicleId == $vehicleArchiveHistory['vehicle_id'] && $vehicleArchiveHistory['event'] == 'Archived'){
                            if($selectedDateValue != $vehicleHistoryEventStartDate || $selectedDateValueEnd != $vehicleHistoryEventEndDate){
                                $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$currentValueCount;
                            } else {
                                $currentCost = $currentCost + ($costValue/$daysInCurMonth)*$eventDifferenceDays;
                            }
                        } else {
                            $currentCost = $currentCost + ($costValue/$diffDaysCounts)*$currentValueCount;
                        }
                        $currentDate = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('m-Y');
                        $currentDateValue = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('d M Y');
                    }
                }
            }
            $totalDaysCountFinal = (float)$totalDaysCountFinal + (float)$totalDaysCount;
        }
        $currentMonthData['currentCost'] = number_format($currentCost,2,'.','');
        $currentMonthData['currentDate'] = $currentDate;
        $currentMonthData['currentDateValue'] = $currentDateValue;
        $currentMonthData['totalDaysCount'] = $totalDaysCountFinal;
        $currentMonthData['currentMonthDates'] = $currentMonthDates;
        return $currentMonthData;
    }

    private function getTotalDaysCount($selectedDate,$startDate,$endDate) {
        $monthStartDate = Carbon::parse($selectedDate)->firstOfMonth();
        $monthEndDate = Carbon::parse($selectedDate)->endOfMonth();
        if ($startDate->lt($monthStartDate)) {
            $startDate = Carbon::parse($selectedDate)->firstOfMonth();
        }

        if ($endDate->gt($monthEndDate)) {
            $endDate = Carbon::parse($selectedDate)->endOfMonth();
        }

        $days = $startDate->diffInDays($endDate);

        return $days + 1;
    }

    /* Optimizing fleet-cost not in use currently */
    //Calculate Monthly cost
    function calcMonthlyCurrentData($costs_json, $selectedDate, $vehicleId, $vehicleArchiveHistory,$vehicleDtAddedToFleet=null,$isInsuranceCostOverride='N/A',$isTelematicsCostOverride='N/A',$isBasedIncurrentDate=false,$typeofCost = '',$selectedDateValue=null,$type='',$isVehicleTax=0) {
        $costs_array = json_decode($costs_json, true);
        $currentCost = 0;
        $currentDate = '';
        $currentDateValue = '';
        $currentMonthData = [];

        //Get final min and max date for the current month cost
        $finalDates = $this->getFinalCostDate($costs_array,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,$isTelematicsCostOverride,$typeofCost,$isVehicleTax);

        $finalCostAndDays = $this->getFinalCostAndDays($costs_array, $selectedDate, $typeofCost, $finalDates, $vehicleDtAddedToFleet, $isInsuranceCostOverride, $isTelematicsCostOverride);

        $currentMonthData['currentCost'] = number_format($finalCostAndDays['currentCost'] ,2,'.','');
        $currentMonthData['currentDate'] = $finalCostAndDays['currentDate'];
        $currentMonthData['currentDateValue'] = $finalCostAndDays['currentDateValue'];
        $currentMonthData['totalDaysCount'] = $finalCostAndDays['totalDaysCount'];
        $currentMonthData['currentMonthDates'] = $finalCostAndDays['currentMonthDates'];
        return $currentMonthData;

    }

    private function getFinalCostDate($costs_array,$selectedDate,$vehicleId,$vehicleArchiveHistory,$vehicleDtAddedToFleet,$isInsuranceCostOverride,$isTelematicsCostOverride,$typeofCost="",$isVehicleTax=0) {
        //$vehicleDtAddedToFleetStartOfMonth = Carbon::parse($vehicleDtAddedToFleet)->startOfMonth();
        $selectedDateValue = Carbon::parse($selectedDate)->startOfMonth();
        $dates = [];
        $fromDate = Carbon::parse($selectedDate)->firstOfMonth()->format('Y-m-d');
        $fromDate = new \DateTime($fromDate);
        $toDate = Carbon::parse($selectedDate)->endOfMonth()->format('Y-m-d');
        $toDate = new \DateTime($toDate);

        //check if cost has only one range and cost is continues == true
        if (count($costs_array) == 1 && $costs_array[0]['cost_continuous'] == "true") {
            $costs_array = $costs_array[0];
            $jsonStartDate = new \DateTime($costs_array['cost_from_date']);
            $jsonEndDate = $toDate;

            //check if month start date is less than cost start date then cost will be calculated from the month start date
            if ($fromDate < $jsonStartDate) {
                $fromDate = $jsonStartDate;
            }

            //check if month end date is greater than cost end date then cost will be calculated to the json end date.
            if ($toDate > $jsonEndDate) {
                $toDate = $jsonEndDate;
            }

        } else {

            $datesCollect = collect($costs_array);

            //get min and max date from multiple date range
            $minMaxDates = $this->getMinMaxDate($datesCollect,$selectedDate);

            $costFromDate = new \DateTime($minMaxDates['min_date']);
            $costToDate = new \DateTime($minMaxDates['max_date']);

            //check if month start date is less than cost start date then cost will be calculated from the month start date
            if ($fromDate < $costFromDate) {
                $fromDate = $costFromDate;
            }

            //check if month end date is greater than cost end date then cost will be calculated to the json end date.
            if ($toDate > $costToDate) {
                $toDate = $costToDate;
            }

        }

        //check if vehicle is archived then cost will be not calculated after archive date
        if (isset($vehicleArchiveHistory->event) && $vehicleArchiveHistory->event == 'Archived') {
            $archiveDate = new \DateTime($vehicleArchiveHistory->event_date_time);
            if ($toDate > $archiveDate) {
                $toDate = $archiveDate;
            }
        }
        
        //during above condition if from date is greater than to date then cost will not applicable
        if ($fromDate > $toDate) {
            $dates['status'] = false;
        } else {
            $dates['status'] = true;
        }
        $dates['from_date'] = $fromDate;
        $dates['to_date'] = $toDate;

        return $dates;
    }

    private function getMinMaxDate($dates,$selectedDate) {

        $result = array();

        $min = '';
        $max = '';
        if (count($dates) > 0) {

                foreach ($dates as $date) {
                    $minDate = new \DateTime($date['cost_from_date']);

                    if ($date['cost_continuous'] == "true")  {
                        $maxDate = Carbon::parse($selectedDate)->endOfMonth()->format('Y-m-d');
                        $maxDate = new \DateTime($maxDate);
                    } else {
                        $maxDate = new \DateTime($date['cost_to_date']);
                    }

                    if ($min == '') {
                        $min = $minDate;
                    }

                    if ($max == '') {
                        $max = $maxDate;
                    }

                    if ($min > $minDate) {
                        $min = $minDate;
                    }

                    if ($max < $maxDate) {
                        $max = $maxDate;
                    }
                }
        }

        if ($min == "" && $max == "") {
            $result['status'] = false;
            $result['min_date'] = $min;
            $result['max_date'] = $max;
        } else {
            $result['status'] = true;
            $result['min_date'] = $min->format('Y-m-d');
            $result['max_date'] = $max->format('Y-m-d');

        }

        return $result;
    }

    private function getFinalCostAndDays($costs_array,$selectedDate,$typeofCost,$finalDays,$vehicleDtAddedToFleet,$isInsuranceCostOverride,$isTelematicsCostOverride) {

        $cost = 0;
        $totalDays = 0;
        $currentMonthDates = [];
        $currentMonthData = [];
        $currentDate = '';
        $currentDateValue = '';

        $monthStartDate = Carbon::parse($selectedDate)->startOfMonth();
        $monthEndDate = Carbon::parse($selectedDate)->endOfMonth();
        $selectedMonth = Carbon::parse($selectedDate)->format('m');
        $selectedYear = Carbon::parse($selectedDate)->format('Y');
        $monthDays = $monthStartDate->diffInDays($monthEndDate) + 1;

        //check if final startDate is greater than final endDate with return form the function "getFinalCostDate"
        if ($finalDays['status'] == false) {
            $currentMonthData['currentCost'] = number_format(0,2,'.','');
            $currentMonthData['currentDate'] = $currentDate;
            $currentMonthData['currentDateValue'] = $currentDateValue;
            $currentMonthData['totalDaysCount'] = $totalDays;
            $currentMonthData['currentMonthDates'] = $currentMonthDates;
            return $currentMonthData;
        }

        //check if cost array has only one element with cost continuous
        if (count($costs_array) == 1 && $costs_array[0]['cost_continuous'] == "true") {

            $costs_array = $costs_array[0];
            $jsonStartDate = new \DateTime($costs_array['cost_from_date']);
            $jsonEndDate = new \DateTime(Carbon::parse($selectedDate)->endOfMonth());

            if ($jsonStartDate < new \DateTime($monthStartDate->format('Y-m-d'))) {
                $jsonStartDate = new \DateTime($monthStartDate->format('Y-m-d'));
            }

            if ($jsonStartDate < $finalDays['from_date']) {
                $jsonStartDate = $finalDays['from_date'];
            }

            if ($jsonEndDate > $finalDays['to_date']) {
                $jsonEndDate = $finalDays['to_date'];
            }

            $checkDateAddedToFleet = 0;

            if (isset($costs_array['json_type']) && $costs_array['json_type'] == 'monthlyVehicleTax' && $vehicleDtAddedToFleet != null) {
                $checkDateAddedToFleet = 1;
            }

            if ($isInsuranceCostOverride === 0 && $vehicleDtAddedToFleet != null) {
                $checkDateAddedToFleet = 1;
            }

            if ($isTelematicsCostOverride === 0 && $vehicleDtAddedToFleet != null) {
                $checkDateAddedToFleet = 1;
            }

            if ($checkDateAddedToFleet == 1) {
                $vehicleDtAddedToFleetObject = new \DateTime($vehicleDtAddedToFleet);
                if ($jsonStartDate < $vehicleDtAddedToFleetObject) {
                    $jsonStartDate = $vehicleDtAddedToFleetObject;
                }
            }

            if ($jsonStartDate > $jsonEndDate) {
                $singleCost = 0;
                $totalDays = 0;
            } else {
                $totalDays = $jsonStartDate->diff($jsonEndDate)->d + 1;
                $singleCost = (float)$costs_array['cost_value']/(float)$monthDays * $totalDays;
            }

            $currentMonthDates = [
                [
                    'start_date' => $jsonStartDate->format('Y-m-d'),
                    'end_date' => $jsonEndDate->format('Y-m-d')
                ]
            ];

            $totalDays = (float)$totalDays;
            $cost = $singleCost;
            $currentDate = Carbon::parse($costs_array['cost_from_date'])->format('m-Y');
            $currentDateValue = Carbon::parse($costs_array['cost_from_date'])->format('d M Y');

        } else {

            //check if $costs_array has valid array
            if (is_array($costs_array)) {

                foreach ($costs_array as $single) {

                    $startDate = new \DateTime($single['cost_from_date']);

                    $singleCost = 0;

                    //check if cost_continuous is true than cost will be calculated till end of the month
                    if ($single['cost_continuous'] == "true") {
                        $endDate = new \DateTime(Carbon::parse($selectedDate)->endOfMonth()->format('Y-m-d'));
                    } else {
                        $endDate = new \DateTime($single['cost_to_date']);
                    }

                    $isCalculate = 0;
                    //check current month dates is between cost range ex. we are finding the cost for FEB and cost date range is like 1 Jan to 31 March
                    if (Carbon::parse($startDate->format('Y-m-d'))->lt($monthStartDate) && Carbon::parse($endDate->format('Y-m-d'))->gt($monthEndDate)) {
                        $isCalculate = 1;
                    }

                    //check if given date range is associated with selected month
                    if (($startDate->format('m') == $selectedMonth && $startDate->format('Y') == $selectedYear) || ($endDate->format('m') == $selectedMonth && $endDate->format('Y')  == $selectedYear) ) {
                        $isCalculate = 1;
                    }

                    //Only calculate cost if it will fall in above conditions
                    if ($isCalculate == 1) {
                        $dateRange = [
                            'start_date' => $startDate->format('Y-m-d'),
                            'end_date' => $endDate->format('Y-m-d')
                        ];
                        array_push($currentMonthDates,$dateRange);

                        if (Carbon::parse($endDate->format('Y-m-d'))->gt($monthEndDate)) {
                            $endDate = new \DateTime($monthEndDate->format('Y-m-d'));
                        }

                        if (Carbon::parse($startDate->format('Y-m-d'))->lt($monthStartDate)) {
                            $startDate = new \DateTime($monthStartDate->format('Y-m-d'));
                        }

                        if ($startDate < $finalDays['from_date']) {
                            $startDate = $finalDays['from_date'];
                        }

                        if ($endDate > $finalDays['to_date']) {
                            $endDate = $finalDays['to_date'];
                        }

                        //check for vehicle tax and date added to fleet
                        $checkDateAddedToFleet = 0;

                        if (isset($single['json_type']) && $single['json_type'] == 'monthlyVehicleTax' && $vehicleDtAddedToFleet != null) {
                            $checkDateAddedToFleet = 1;
                        }

                        if ($isInsuranceCostOverride === 0 && $vehicleDtAddedToFleet != null) {
                            $checkDateAddedToFleet = 1;
                        }



                        if ($isTelematicsCostOverride === 0 && $vehicleDtAddedToFleet != null) {
                            $checkDateAddedToFleet = 1;
                        }

                        if ($checkDateAddedToFleet == 1) {

                            $vehicleDtAddedToFleetObject = new \DateTime($vehicleDtAddedToFleet);
                            if ($startDate < $vehicleDtAddedToFleetObject) {
                                $startDate = $vehicleDtAddedToFleetObject;
                            }
                        }


                        if ($startDate > $endDate) {
                            $singleCost = 0;
                        }else if ($endDate < $finalDays['from_date']) {
                            $singleCost = 0;
                        } else {
                            //final calculation formula
                            $singleDays = $startDate->diff($endDate)->d + 1;
                            $singleCost = (float)$single['cost_value']/(float)$monthDays * $singleDays;
                            $totalDays = (float)$totalDays + $singleDays;
                        }

                        $currentDate = Carbon::parse($single['cost_from_date'])->format('m-Y');
                        $currentDateValue = Carbon::parse($single['cost_from_date'])->format('d M Y');
                    }

                    $cost = (float)$cost + $singleCost;
                }
            } else {
                $cost = 0;
                $currentDate = '';
                $currentDateValue = '';
            }
        }
        $currentMonthData['currentCost'] = number_format($cost,2,'.','');
        $currentMonthData['currentDate'] = $currentDate;
        $currentMonthData['currentDateValue'] = $currentDateValue;
        $currentMonthData['totalDaysCount'] = $totalDays;
        $currentMonthData['currentMonthDates'] = $currentMonthDates;
        return $currentMonthData;
    }

    function calcCurrentMonthBasedOnPeriod($manual_cost_adjustments, $selectedDate, $vehicleId=null, $vehicleArchiveHistory=null,$typeOfCost='')
    {
        $currentMonthData = [];
        $currentDate = '';
        $currentDateValue = '';
        $currentCost = 0;
        $totalDaysCount = '';
        $totalDaysCountFinal = 0;
        $currentMonthDates = [];
        $vehicleHistoryEventDays = '';

        $selectedDateValue = Carbon::parse($selectedDate)->startOfMonth();

        $vehicleHistoryEventDate = Carbon::parse($vehicleArchiveHistory['event_date_time']);

        if($selectedDateValue->gt($vehicleHistoryEventDate) && $vehicleArchiveHistory['event'] == "Archived") {
            $currentMonthData['currentCost'] = 0;
            $currentMonthData['currentDate'] = '';
            $currentMonthData['currentDateValue'] = 0;
            $currentMonthData['totalDaysCount'] = 0;
            $currentMonthData['currentMonthDates'] = '';
            return $currentMonthData;
        }


        $finalCost = 0;
        $totalDaysCountOfTheMonth = 0;

        foreach ($manual_cost_adjustments as $key => $manual_cost_adjustment) {
            $costFromDate = new \DateTime($manual_cost_adjustment['cost_from_date']);
            $costToDate = new \DateTime($manual_cost_adjustment['cost_to_date']);
            $fromDate = new \DateTime(Carbon::parse($selectedDate)->firstOfMonth()->format('Y-m-d'));
            $toDate = new \DateTime(Carbon::parse($selectedDate)->endOfMonth()->format('Y-m-d'));
            $costFromMonth = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('m');
            $costFromYear = Carbon::parse($manual_cost_adjustment['cost_from_date'])->format('Y');
            $costToMonth = Carbon::parse($manual_cost_adjustment['cost_to_date'])->format('m');
            $costToYear = Carbon::parse($manual_cost_adjustment['cost_to_date'])->format('Y');
            $selectedMonth = Carbon::parse($selectedDate)->format('m');
            $selectedYear = Carbon::parse($selectedDate)->format('Y');

            if (($costFromMonth == $selectedMonth && $costFromYear == $selectedYear) || ($costToMonth == $selectedMonth && $costToYear == $selectedYear) ) {

                $dateRange = [
                    'start_date' => $costFromDate->format('Y-m-d'),
                    'end_date' => $costToDate->format('Y-m-d')
                ];

                array_push($currentMonthDates,$dateRange);
            }

            $totalDaysCount = Carbon::parse($manual_cost_adjustment['cost_from_date'])->diffInDays(Carbon::parse($manual_cost_adjustment['cost_to_date'])) + 1;
            $totalDaysCountFinal = $totalDaysCountFinal + $totalDaysCount;
            if ($fromDate < $costFromDate) {
                $fromDate = $costFromDate;
            }
            if ($toDate > $costToDate) {
                $toDate = $costToDate;
            }

            if ($vehicleArchiveHistory != null && $vehicleArchiveHistory->event == 'Archived') {
                $vehicleArchiveDate = new \DateTime($vehicleArchiveHistory['event_date_time']);
                if ($toDate > $vehicleArchiveDate) {
                    $toDate = $vehicleArchiveDate;
                }
            }
            if ($fromDate > $toDate) {
                $cost = 0;
            } else {
            $daysCount = Carbon::parse($fromDate->format('Y-m-d'))->diffInDays(Carbon::parse($toDate->format('Y-m-d'))) + 1;
                $cost = (float)$manual_cost_adjustment['cost_value']/$totalDaysCount * $daysCount;
                $totalDaysCountOfTheMonth = $totalDaysCountOfTheMonth + $daysCount;
            }

            $finalCost = $finalCost + $cost;

        }

        $currentMonthData['currentCost'] = number_format($finalCost,2,'.','');
        $currentMonthData['currentDate'] = $currentDate;
        $currentMonthData['currentDateValue'] = $currentDateValue;
        $currentMonthData['totalDaysCount'] = $totalDaysCountOfTheMonth;
        $currentMonthData['currentMonthDates'] = $currentMonthDates;
        return $currentMonthData;
    }

    function getFleetCostValueForDate($costArray,$date,$vehicleArchiveHistory = null,$vehicleDtAddedToFleet = null,$isOverrideCost = null) {

        $currentMonthData['currentCost'] = number_format(0 ,2,'.','');
        $currentMonthData['currentDate'] = Carbon::parse($date)->format('m-Y');
        $currentMonthData['currentDateValue'] = 0;
        $currentMonthData['totalDaysCount'] = 0;
        $currentMonthData['currentMonthDates'] = [];

        $nowDateObj = Carbon::parse($date);

        $costArray = json_decode($costArray,true);

        if ($vehicleArchiveHistory != null && isset($vehicleArchiveHistory->event) && $vehicleArchiveHistory->event == 'Archived') {
            $archiveDate = Carbon::parse($vehicleArchiveHistory->event_date_time)->format('Y-m-d');
            $archiveDateObj = Carbon::parse($archiveDate);
            if ($nowDateObj->gt($archiveDateObj)) {
                $currentMonthData['currentCost'] = number_format(0 ,2,'.',',');
                return $currentMonthData;
            }
        }


        $checkDateAddedToFleet = 0;

        if (isset($costArray[0]['json_type']) && $costArray[0]['json_type'] == 'monthlyVehicleTax' && $vehicleDtAddedToFleet != null) {
            $checkDateAddedToFleet = 1;
        }

        if ($isOverrideCost === 0 && $vehicleDtAddedToFleet != null) {
            $checkDateAddedToFleet = 1;
        }


        if ($checkDateAddedToFleet == 1) {
            $vehicleDtAddedToFleetObject = Carbon::parse($vehicleDtAddedToFleet);

            if ($vehicleDtAddedToFleetObject->gt($nowDateObj)) {
                $currentMonthData['currentCost'] = number_format(0 ,2,'.',',');

                return $currentMonthData;
            }

        }

        if ($costArray != "" && count($costArray) > 0) {

            foreach ($costArray as $singleCost) {
                $fromDateObj = Carbon::parse($singleCost['cost_from_date']);

                if ($singleCost['cost_continuous'] == "true") {
                    $toDateObj = Carbon::parse($date)->endOfMonth();
                } else {
                    $toDateObj = Carbon::parse($singleCost['cost_to_date']);
                }


                if ($nowDateObj->gte($fromDateObj) && $nowDateObj->lte($toDateObj)) {
                    $currentMonthData['currentCost'] = $singleCost['cost_value'];
                    $currentMonthData['currentCost'] = number_format(str_replace(',','',$currentMonthData['currentCost']) ,2,'.',',');
                    return $currentMonthData;
                }

            }

        }

        $currentMonthData['currentCost'] = number_format($currentMonthData['currentCost'] ,2,'.',',');
        return $currentMonthData;

    }

    function getNextServiceInspectionDistance($odometerReading,$distanceInterval) {
        $interval = (int) str_replace(",","",$distanceInterval);
        if ($interval != "" || $interval!=null) {
            $odometerReading = str_replace("miles","",$odometerReading);
            $odometerReading = str_replace("km","",$odometerReading);
            $odometerReading = str_replace(" ","",$odometerReading);
            $odometerReading = str_replace(",","",$odometerReading);

            //FLEE-6674
            // $value = $odometerReading/$interval;
            // $value = (int) $value;
            // $value = $value*$interval+$interval;
            $value = $odometerReading + $interval;

            return $value;
        } else {
            return 0;
        }

    }

}