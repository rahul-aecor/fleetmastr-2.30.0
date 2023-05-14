<?php

namespace App\Presenters;

use Pingpong\Presenters\Presenter;
use Carbon\Carbon;

class DefectPresenter extends Presenter
{
    public function label_class_for_status()
    {        
        if (strtolower($this->status) == 'reported' || strtolower($this->status) == 'repair rejected') {
            return 'label-danger';
        }
        if (strtolower($this->status) == 'resolved') {
            return 'label-success';
        }
        if (strtolower($this->status) == 'acknowledged' || strtolower($this->status) == 'allocated' || strtolower($this->status) == 'under repair' || strtolower($this->status) == 'discharged') {
            return 'label-workshop';
        }
        return 'label-status-default';
    }   

    public function formattedCreatedAt()
    {
        return $this->created_at->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }

    public function formattedReportDatetime()
    {
        return $this->report_datetime->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }

    public function formattedResolvedDatetime()
    {
        return Carbon::parse($this->resolved_datetime_original)->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }

    public function formattedUpdatedAt()
    {
        return $this->updated_at->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }

    public function formattedCost()
    {
        return number_format($this->cost,2);
    }
}
