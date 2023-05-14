<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Services\CustomReportService;
use App\Repositories\CustomReportRepository;
use App\Repositories\CustomReportDownloadRepository;
use App\Repositories\ReportRepository;
use App\Http\Requests\StoreCustomReportRequest;
use App\Services\UserService;
use App\Custom\Facades\GridEncoder;
use Carbon\Carbon as Carbon;
use Input;
use View;
Use Auth;
use JavaScript;

class CustomReportController extends Controller
{
    public $title= 'Reports';

    public function __construct(CustomReportService $service) {
        View::share( 'title', $this->title );
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        View::share('title', 'Reports');
        $resultVehicle = (new UserService())->getAllVehicleLinkedData(true);
        $allVehicleDivisionsList = $resultVehicle['vehicleRegions'];
        $vehicleDivisions = $resultVehicle['vehicleDivisions'];
        $categories = $this->service->reportCategories()->pluck('name', 'id')->toArray();
        $standardCategories = $this->service->reportStandardCategories()->pluck('name', 'id')->toArray();
        $getAllCategories = $this->service->getCategoryData()->pluck('name', 'id')->toArray();
        foreach ($categories as $key => $category) {
            $splittedCategory = explode(" ", $category);
            $customString = $splittedCategory[0].' '.mb_strtolower($splittedCategory[1]);
            $categories[$key] = $customString;
        }
        $selectedRegions = null;
        if(isset($request->tab) && $request->tab == 'download') {
            setcookie('customreport_ref_tab', 'downloads_tab');
            $selectedTab = 'downloads_tab';
        } else {
            $selectedTab = isset($_COOKIE['customreport_ref_tab']) ? str_replace("#", "", $_COOKIE['customreport_ref_tab']) : 'reports_tab';
        }

        JavaScript::put([
            'isRegionLinkedInVehicle' => env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'),
            'allVehicleDivisionsList' => $allVehicleDivisionsList
        ]);

        return view('custom_reports.index', compact('categories', 'allVehicleDivisionsList', 'vehicleDivisions', 'selectedRegions', 'selectedTab', 'standardCategories', 'getAllCategories'));
    }

    /**
     * @return [type]
     */
    public function anyData()
    {
        return GridEncoder::encodeRequestedData(new CustomReportRepository(), Input::all());
    }

    /**
     * @return [type]
     */
    public function anyReportDownloadData()
    {
        return GridEncoder::encodeRequestedData(new CustomReportDownloadRepository(), Input::all());
    }

    /**
     * @return [type]
     */
    public function anyReportData()
    {
        return GridEncoder::encodeRequestedData(new ReportRepository(), Input::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(404);
        View::share('title', 'Create Custom Report');
        $categories = $this->service->reportCategories();
        $exitingReportCategories = $this->service->exitingReportCategories();
        $report = null;
        $reportColumns = null;
        $dataSet = $this->service->getReportDataSet();
        $reportDataSet = $dataSet->groupBy('model_type');
        foreach ($categories as $key => $category) {
            $splittedCategory = explode(" ", $category['name']);
            $customString = $splittedCategory[0].' '.mb_strtolower($splittedCategory[1]);
            $categories[$key]['name'] = $customString;
        }
        
        JavaScript::put([
            'categoryList' => $categories->toArray(),
            'reportCategoryId' => null,
            'exitingReportCategories' => $exitingReportCategories,
            'reportDataSet' => $dataSet->keyBy('id'),
            'isRegionLinkedInVehicle' => env('IS_DIVISION_REGION_LINKED_IN_VEHICLE')
        ]);

        return view('custom_reports.create', compact('categories', 'report', 'reportDataSet', 'reportColumns'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomReportRequest $request)
    {
        $saveData = $this->service->create($request->all());
        if($saveData) {
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        } else {
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }
        return redirect("reports/");
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
        View::share('title', 'Edit Custom Report');

        $report = $this->service->report($id);
        $reportColumnsByOrder = $report->reportColumns()->orderBy('order')->get()->pluck('report_dataset_id');
        $reportColumns = $report->reportColumns->pluck('report_dataset_id')->toArray();

        $categories = $this->service->reportCategories();
        $exitingReportCategories = $this->service->exitingReportCategories();
        $dataSet = $this->service->getReportDataSet();
        $reportDataSet = $dataSet->groupBy('model_type');

        $categoryDataSet = $this->service->getCateoryDataset($report->report_category_id);
        $reportDataSet = $categoryDataSet->dataset->groupBy('model_type');

        JavaScript::put([
            'categoryList' => $categories->toArray(),
            'reportCategoryId' => $report->report_category_id,
            'exitingReportCategories' => $exitingReportCategories,
            'reportDataSet' => $dataSet->sortBy('order')->keyBy('id'),
            'page' => 'edit',
            'reportColumns' => $reportColumnsByOrder,
            'reportId' => $id
        ]);

        return view('custom_reports.edit', compact('categories', 'report', 'reportDataSet', 'reportColumns'));
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
        $updateData = $this->service->update($id, $request->all());
        if($updateData) {
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        } else {
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }
        return redirect("reports/");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $report = $this->service->report($id)->delete();
        if ($report) {
            flash()->success(config('config-variables.flashMessages.reportDeleted'));
        } else {
            flash()->success(config('config-variables.flashMessages.dataNotDeleted'));
        }
        return redirect('reports');
    }

    public function getReportColumns(Request $request, $id)
    {
        $report = $this->service->report($id);
        if($report->is_custom_report) {
            $reportColumns = $report->reportColumns->pluck('report_dataset_id');
            $dataSet = $this->service->getReportDataSetColumns($id, $reportColumns);
        } else {
            if($report->slug == 'standard_vor_defect_report') {
                $dataSet = config('config-variables.standard_reports.'.$report->slug);
                $allRegions = Auth::user()->regions->lists('name')->toArray();
                $dataSet = array_merge($dataSet, $allRegions, ['Grand Total']);
            } else {
                $dataSet = config('config-variables.standard_reports.'.$report->slug);
            }
        }
        $type = 'create';
        return view('custom_reports.report_columns', compact('dataSet', 'report', 'type'));
    }

    public function viewAllReportCategories()
    {
        return $this->service->getCategoryData()->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addReportCategory(Request $request)
    {
        $this->service->addReportCategory($request->all());
        return $this->service->getCategoryData()->toArray();
    }

    public function updateCategoryName(Request $request)
    {
        $this->service->updateCategoryName($request->all());
        return $this->service->getCategoryData()->toArray();
    }

    public function deleteReportCategory(Request $request)
    {
        $this->service->deleteReportCategory($request->id);
        return $this->service->getCategoryData()->toArray();
    }

    public function getCateoryDataset(Request $request)
    {
        $dataSet = $this->service->getCateoryDataset($request->category_id);
        $reportDataSet = $dataSet->dataset->groupBy('model_type');
        $report = null;
        $reportColumns = null;
        return view('_partials.custom_reports.set_category_dataset', compact('reportDataSet', 'reportColumns', 'report'))->render();
    }

    public function getCustomReports($reportId)
    {
        $report = $this->service->report($reportId);
        if(!Report::checkTelematicsIsEnableAndReportAvailable($report->slug)) {
            return redirect('reports');
        }
        View::share('title', 'Create Custom Report');
        $categories = $this->service->getCateoryDataset($report->report_category_id);
        $exitingReportCategories = $this->service->exitingReportCategories();
        // $report = null;
        $reportColumns = null;
        $dataSet1 = $this->service->getReportDataSet();

        $dataSet = $this->service->getCateoryDataset($categories['id']);
        $reportDataSet = $dataSet->dataset->groupBy('model_type');

        $resultVehicle = (new UserService())->getAllVehicleLinkedData(true);
        $allVehicleDivisionsList = $resultVehicle['vehicleRegions'];
        $vehicleDivisions = $resultVehicle['vehicleDivisions'];
        if($report->slug) {
            $selectedRegions = Auth::user()->regions->lists('id')->toArray();
        } else {
            $downloadReport = $this->service->downloadReport($report->report_category_id);
            $selectedRegions = $downloadReport->regions->pluck('id')->toArray();
        }

        JavaScript::put([
            'categoryList' => $categories->toArray(),
            'reportCategoryId' => null,
            'exitingReportCategories' => $exitingReportCategories,
            'reportDataSet' => $dataSet1->keyBy('id'),
            // 'allVehicleDivisionsList' => $allVehicleDivisionsList,
            'allVehicleDivisionsList' => $resultVehicle['vehicleOnlyRegions'],
            'reportSlug' => $report->slug,
            'page' => 'edit',
            'isRegionLinkedInVehicle' => env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'),
            'reportFor' => $report->report_for
        ]);

        return view('custom_reports.create', compact('categories', 'report', 'reportDataSet', 'reportColumns', 'allVehicleDivisionsList', 'vehicleDivisions', 'selectedRegions'));
    }

    public function generateCustomReport(Request $request)
    {
        $saveData = $this->service->generateCustomReport($request->all());
        if($saveData) {
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        } else {
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }
        return redirect("reports/");
    }

    public function saveDownloadReport(Request $request)
    {
        $data = $request->all();
        $saveData = $this->service->saveDownloadReport($data);
        if($saveData) {
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        } else {
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }
        return redirect('reports');
    }

    public function updateDatasetOrder(Request $request)
    {
        $this->service->updateDatasetColumnOrders($request->dataset_order, $request->report_id);
        return response()->json(['status' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteDownloadReport(Request $request, $id)
    {
        $report = $this->service->downloadReport($id)->delete();
        if ($report) {
            flash()->success(config('config-variables.flashMessages.reportDeleted'));
        } else {
            flash()->success(config('config-variables.flashMessages.dataNotDeleted'));
        }
        return redirect('reports');
    }

    public function downloadReportCriteria(Request $request, $id)
    {
        $downloadReport = $this->service->downloadReport($id);
        $selectedRegions = $downloadReport->regions->pluck('id')->toArray();
        $report = $this->service->report($downloadReport->report_id);

        $reportCategorySlug = $report->category->slug;
        if($reportCategorySlug == 'vehicle_location') {
            $dateRange = Carbon::parse($downloadReport->date_from)->format('H:i:s d M Y').' - '.Carbon::parse($downloadReport->date_to)->format('H:i:s d M Y');
        } else {
            $dateRange = Carbon::parse($downloadReport->date_from)->format('d M Y').' - '.Carbon::parse($downloadReport->date_to)->format('d M Y');
        }

        $resultVehicle = (new UserService())->getAllVehicleLinkedData(true);
        $allVehicleDivisionsList = $resultVehicle['vehicleRegions'];
        $vehicleDivisions = $resultVehicle['vehicleDivisions'];
        $categories = $this->service->reportCategories()->pluck('name', 'id')->toArray();

        if(isset($downloadReport->report_columns) && $downloadReport->report_columns) {
            $dataSet = explode(",", $downloadReport->report_columns);
        } else {
            if($report->is_custom_report) {
                $reportColumns = $report->reportColumns->pluck('report_dataset_id');
                $dataSet = $this->service->getReportDataSetColumns($report->id, $reportColumns);
            } else {
                if($report->slug == 'standard_vor_defect_report') {
                    $dataSet = config('config-variables.standard_reports.'.$report->slug);
                    $allRegions = Auth::user()->regions->lists('name')->toArray();
                    $dataSet = array_merge($dataSet, $allRegions, ['Grand Total']);
                } else {
                    $dataSet = config('config-variables.standard_reports.'.$report->slug);
                }
            }
        }

        return view('custom_reports.download_report_criteria', compact('dataSet', 'dateRange', 'allVehicleDivisionsList', 'vehicleDivisions', 'categories', 'selectedRegions', 'report', 'downloadReport'));
    }
}
