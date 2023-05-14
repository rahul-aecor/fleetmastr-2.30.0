<?php
namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCheck;
use App\Models\AssetDefect;
use App\Models\ColumnManagements;
use App\Repositories\CustomReportRepository;
use App\Repositories\CustomReportDownloadRepository;
use Carbon\Carbon;
use App\Jobs\CreateDownloadableReport;
use App\Jobs\CreateCustomReport;
use App\Services\Report as ReportService;

class CustomReportService
{
	public function __construct(CustomReportRepository $repository, CustomReportDownloadRepository $downloadReportRepository, ReportService $reportService)
    {
    	$this->repository = $repository;
		$this->downloadReportRepository = $downloadReportRepository;
		$this->reportService = $reportService;
    }

	public function report($id)
	{
		return $this->repository->report($id);
	}

	public function reportCategories()
	{
		return $this->repository->reportCategories();
	}

	public function reportStandardCategories()
	{
		return $this->repository->reportStandardCategories();
	}

	public function exitingReportCategories()
	{
		return $this->repository->exitingReportCategories();
	}

	public function create($data)
	{
		return $this->repository->create($data);
	}

	public function update($id, $data)
	{
		$report = $this->repository->report($id);
		return $this->repository->update($report, $data);
	}

	public function getCategoryData()
	{
		return $this->repository->getCategoryData();
	}

	public function addReportCategory($data)
	{
		return $this->repository->addReportCategory($data);
	}

	public function updateCategoryName($data)
	{
		return $this->repository->updateCategoryName($data);
	}

	public function deleteReportCategory($id)
	{
		return $this->repository->deleteReportCategory($id);
	}

	public function getCateoryDataset($category_id)
	{
		return $this->repository->getCateoryDataset($category_id);
	}

	public function getReportDataSet()
	{
		return $this->repository->getReportDataSet();
	}

	public function getReportDataSetColumns($id, $reportColumns)
	{
		return $this->repository->getReportDataSetColumns($id, $reportColumns);
	}

	public function saveDownloadReport($data)
	{
		if($data['report_slug'] == 'standard_vehicle_location_report') {
			$dateFormat = 'H:i:s d M Y';
			$dbFormat = 'Y-m-d H:i:s';
		} else {
			$dateFormat = 'd M Y';
			$dbFormat = 'Y-m-d';
		}
		if(isset($data['date_range']) && !empty($data['date_range'])) {
			$date = explode(" - ", $data['date_range']);
			$data['date_from'] = Carbon::createFromFormat($dateFormat, $date[0])->format($dbFormat);
			$data['date_to'] = Carbon::createFromFormat($dateFormat, $date[1])->format($dbFormat);
		} else {
			$data['date_from'] = Carbon::now()->format($dbFormat);
			$data['date_to'] = Carbon::now()->format($dbFormat);
		}

		$data['created_at'] = Carbon::now();

		$data['report_dataset_columns'] = $this->fetchReportDataSetColumns($data);

		$reportDownload = $this->downloadReportRepository->store($data, null);
		$data['user'] = \Auth::user();

		$report = $this->report($data['report_id']);
		$data['report_description'] = $report->description;

		unset($data['user']['roles']);
		dispatch(new CreateDownloadableReport($data, $reportDownload->id));

		return $reportDownload;
	}

	public function downloadReport($id)
	{
		return $this->downloadReportRepository->downloadReport($id);
	}

	public function updateDatasetColumnOrders($data, $reportId)
	{
		return $this->repository->updateDatasetColumnOrders($data, $reportId);
	}

	public function generateCustomReport($data)
	{
		if($data['report_slug'] == 'standard_vehicle_location_report') {
			$dateFormat = 'H:i:s d M Y';
			$dbFormat = 'Y-m-d H:i:s';
		} else {
			$dateFormat = 'd M Y';
			$dbFormat = 'Y-m-d';
		}
		if(isset($data['date_range']) && !empty($data['date_range'])) {
			$date = explode(" - ", $data['date_range']);
			$data['date_from'] = Carbon::createFromFormat($dateFormat, $date[0])->format($dbFormat);
			$data['date_to'] = Carbon::createFromFormat($dateFormat, $date[1])->format($dbFormat);
		} else {
			$data['date_from'] = Carbon::now()->format($dbFormat);
			$data['date_to'] = Carbon::now()->format($dbFormat);
		}

		if(!isset($data['accessible_regions'])) {
			$data['accessible_regions'] = \Auth::user()->regions->lists('id')->toArray();
		}

		$oldReportData = $this->repository->report($data['report_id']);

		// Update last generated date for the actual report
		$oldReportData->last_downloaded_at = Carbon::now('UTC')->toDateTimeString();
		$oldReportData->save();

		$newReportData = $this->repository->create($data, $oldReportData);

		$data['report_id'] = $newReportData->id;

		$data['created_at'] = Carbon::now();

		$data['report_dataset_columns'] = $this->fetchReportDataSetColumns($data);

		$reportDownload = $this->downloadReportRepository->store($data, $newReportData);
		// $reportDownload = 1;
		$data['user'] = \Auth::user();
		unset($data['user']['roles']);
		dispatch(new CreateCustomReport($data, $reportDownload->id, $newReportData, $oldReportData));

		return $reportDownload;
	}

	private function fetchReportDataSetColumns($data)
	{
		if(isset($data['dataset_order']) && $data['dataset_order']) {

			$reportDataset = json_decode($data['dataset_order'], true);
            $dataset = collect($reportDataset)->sort()->keys()->toArray();

            //SELECT GROUP_CONCAT(CONCAT(title, "|", REPLACE(model_type, 'App\\Models\\', ''))) AS title1 FROM report_dataset ORDER BY FIELD(id,23, 24)
            // $reportDatasetColumnsData = \App\Models\ReportDataset::whereIn('id', $dataset)->selectRaw('GROUP_CONCAT(CONCAT(title, "|", REPLACE(model_type, "App\\Models\\", "")))')->first();

            $reportDatasetColumnsData = \App\Models\ReportDataset::whereIn('id', $dataset)->orderByRaw('FIELD(id, '.implode(",", $dataset).')')->get(['title', 'model_type', 'id']);

            foreach($reportDatasetColumnsData as $value) {

				$reportDatasetColumns[] = $value->title. '|'. str_replace('App\\Models\\', '', $value->model_type);

            }

            $reportDatasetColumns = implode(",", $reportDatasetColumns);

		} else {

			$labels = config('config-variables.standard_reports')[$data['report_slug']];
			$reportDatasetColumns = implode(",", $labels);

		}

		return $reportDatasetColumns;
	}
}