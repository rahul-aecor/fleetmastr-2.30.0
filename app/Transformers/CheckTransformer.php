<?php

namespace App\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\Check;
use App\Models\Defect;
use App\Models\PreexistingDefectAcknowledgement;

class CheckTransformer extends TransformerAbstract {

    public function transform(Check $check)
    {
        $check_defects  = Defect::with('updater')
            ->withTrashed()
            ->where('check_id', $check->id)
            ->get()
            ->toArray();
        
        foreach ($check_defects as $key => $defect) {
            if ($defect['updated_at']) {
                $check_defects[$key]['updated_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $defect['updated_at'])->setTimezone(config('config-variables.displayTimezone'))->toDateTimeString();
            }
            if ($defect['created_at']) {
                $check_defects[$key]['created_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $defect['created_at'])->setTimezone(config('config-variables.displayTimezone'))->toDateTimeString();
            }
            if ($defect['report_datetime']) {
                $check_defects[$key]['report_datetime'] = Carbon::createFromFormat('Y-m-d H:i:s', $defect['report_datetime'])->setTimezone(config('config-variables.displayTimezone'))->toDateTimeString();
            }
        }

        $preexistingDefectAcknowledgement  = PreexistingDefectAcknowledgement::where('check_id', $check->id)
                                ->where('status', 1)
                                ->select('defect_id')
                                ->get()->toArray();
        $preexisting_defects =  Defect::with('updater')
            ->withTrashed()
            ->whereIn('id', $preexistingDefectAcknowledgement)
            ->get()
            ->toArray();
        foreach ($preexisting_defects as $key => $defect) {
            if ($defect['updated_at']) {
                $preexisting_defects[$key]['updated_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $defect['updated_at'])->setTimezone(config('config-variables.displayTimezone'))->toDateTimeString();
            }
            if ($defect['created_at']) {
                $preexisting_defects[$key]['created_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $defect['created_at'])->setTimezone(config('config-variables.displayTimezone'))->toDateTimeString();
            }
            if ($defect['report_datetime']) {
                $preexisting_defects[$key]['report_datetime'] = Carbon::createFromFormat('Y-m-d H:i:s', $defect['report_datetime'])->setTimezone(config('config-variables.displayTimezone'))->toDateTimeString();
            }
        }

        return [
                'history'=> [
                    // 'id' => $history->id,
                    // 'status'=> $history->status,
                    // 'json' => $history->json,
                    'id' => $check->id,
                    'type' => ($check->type == 'Vehicle Check On-call')? 'Vehicle Check (On-call)' : $check->type,
                    'date' => $check->created_at->setTimezone(config('config-variables.displayTimezone'))->format('H:i d M Y'),
                    'report_datetime' => $check->report_datetime->setTimezone(config('config-variables.displayTimezone'))->format('H:i d M Y'),
                    'status' => $check->status,
                    'total_defects' => count($check_defects),
                    'defects' => $check_defects,
                    'preexisting_defects_count' => count($preexistingDefectAcknowledgement),
                    'preexisting_defects' => $preexisting_defects,
                ]
            ];
    }
}
?>