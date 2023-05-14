<?php
namespace App\Repositories;

use Auth;
use DB;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\ReportDataset;
use App\Models\ReportColumn;
use App\Models\ReportDownload;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class CustomReportDownloadRepository extends EloquentRepositoryAbstract {

    public function __construct()
    {
        $this->Database = ReportDownload::join('reports', 'report_downloads.report_id', '=', 'reports.id')
            ->join('report_categories', 'reports.report_category_id', '=', 'report_categories.id')
            ->whereNull('reports.deleted_at');

        if(Auth::check()) {
            $this->Database = $this->Database->where(function($query) {
                $query->where('report_downloads.created_by', Auth::user()->id);
                $query->orWhere(function($query) {
                    $query->whereNull('report_downloads.created_by');
                    $query->where('is_auto_download', '1');
                });
            });
        }

        $this->Database = $this->Database->select('report_downloads.id', 'report_id', 'reports.name', 'reports.description', 'reports.slug',
                'report_categories.name as category_name', 'is_custom_report',
                'report_downloads.date_from', 'report_downloads.date_to', 'report_downloads.filename',
                \DB::raw("CONVERT_TZ(report_downloads.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'created_at'"), 'date_from as daterange', \DB::raw('CASE WHEN is_custom_report = 0 THEN "Standard" ELSE "Custom" END as reporttype'));

        $this->visibleColumns = [
            'reports.id', 'reports.name', 'reports.description', 'reports.slug', 'category_name', 'filename', 'report_downloads.date_from', 'report_downloads.date_to', 'is_custom_report',
            \DB::raw("CONVERT_TZ(reports.created_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'created_at'"), 'date_from as daterange', \DB::raw("CASE WHEN is_custom_report == 0 THEN 'Standard' ELSE 'Custom' END as reporttype")
        ];

        $this->orderBy = [['report_downloads.id', 'DESC']];
    }

    public function downloadReport($id)
    {
        return ReportDownload::find($id);
    }

    public function store($data, $newReportData)
    {
        $reportDownload = new ReportDownload();
        // if($newReportData != null) {
        //     $reportDownload->report_id = $newReportData['id'];
        // } else {
            $reportDownload->report_id = $data['report_id'];
        // }
        $reportDownload->date_from = $data['date_from'];
        $reportDownload->date_to = $data['date_to'];
        $reportDownload->created_at = $data['created_at'];
        if(Auth::check()) {
            $reportDownload->created_by = Auth::user()->id;
        }
        if(isset($data['is_auto_download'])) {
            $reportDownload->is_auto_download = $data['is_auto_download'];
        }

        $reportDownload->report_columns = $data['report_dataset_columns'];

        $reportDownload->save();

        if(isset($data['accessible_regions']) && $data['accessible_regions']) {
            $reportDownload->regions()->sync($data['accessible_regions']);
        } else {
            $userRegions = Auth::user()->regions->lists('id')->toArray();
            $reportDownload->regions()->sync($userRegions);
        }

        if(isset($data['dataset_order']) && $data['dataset_order']) {
            $reportDataset = json_decode($data['dataset_order'], true);
            $dataset = collect($reportDataset)->sort()->keys()->toArray();
            $reportDownload->reportDataset()->sync($dataset);
        }
        return $reportDownload;
    }
}
