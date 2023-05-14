<?php
namespace App\Repositories;

use Auth;
use DB;
use App\Models\Report;
use App\Models\Settings;
use App\Custom\Repositories\EloquentRepositoryAbstract;

class ReportRepository extends EloquentRepositoryAbstract {

    public function __construct()
    {
        // $lastLogin = Report::where('slug', 'standard_last_login_report')->first();

        $this->Database = Report::join('report_categories', 'reports.report_category_id', '=', 'report_categories.id')
            // ->leftjoin(DB::raw('(SELECT report_id, filename, created_at as last_generated_date FROM report_downloads 
            //         WHERE created_by = '.Auth::user()->id.' AND created_at IN (SELECT MAX(created_at) FROM report_downloads WHERE deleted_at IS NULL AND created_by = '.Auth::user()->id.' GROUP BY report_id)) as report_downloads'), 'reports.id', '=', 'report_downloads.report_id')
            ->where('is_custom_report', 0)
            ->groupBy('reports.id')
            ->select('reports.id', 'reports.name', 'reports.description', 'period', 'reports.slug',
                'report_categories.name as category_name', 'reports.last_downloaded_at',
                DB::raw("CONVERT_TZ(reports.last_downloaded_at, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'last_generated_date'")
            );

        $fleetCostSetting = Settings::where('key', 'is_fleetcost_enabled')->first();
        if (!$fleetCostSetting || ($fleetCostSetting && $fleetCostSetting->value != 1)) {
            $this->Database = $this->Database->where('reports.name','!=','Fleet Costs');
        }

        if(setting('is_telematics_enabled') != 1) {
            $this->Database = $this->Database->where('reports.report_type','!=','telematics');
        }

        // $telematicsReports = config('config-variables.telematics_reports');
        // if(setting('is_telematics_enabled') != 1) {
        //     $this->Database = $this->Database->whereNotIn('reports.slug', $telematicsReports);
        // }

        $this->visibleColumns = [
            'reports.id', 'reports.name', 'reports.description', 'category_name', 'period', 'reports.slug',
            DB::raw("CONVERT_TZ(report_downloads.last_generated_date, 'UTC', '".config('config-variables.format.displayTimezone')."') as 'last_generated_date'"), 'filename'
        ];

        $this->orderBy = [['reports.name', 'ASC']];
    }
}
