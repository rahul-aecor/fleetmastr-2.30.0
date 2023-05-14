<?php

namespace App\Presenters;

use Pingpong\Presenters\Presenter;

class CheckPresenter extends Presenter
{
    public function label_class_for_status()
    {
        if (strtolower($this->status) == 'safetooperate') {
            return 'label-warning';
        }
        if (strtolower($this->status) == 'roadworthy') {
            return 'label-success';
        }
        if (strtolower($this->status) == 'unsafetooperate') {
            return 'label-danger';
        }
    }   

    public function status_to_display()
    {
        if (strtolower($this->status) == 'safetooperate') {
            return 'Safe to operate';
        }
        if (strtolower($this->status) == 'roadworthy') {
            return 'Roadworthy';
        }
        if (strtolower($this->status) == 'unsafetooperate') {
            return 'Unsafe to operate';
        }
    }

    public function types_to_display()
    {
        if (strtolower($this->type) == 'vehicle check') {
            return 'Vehicle take out';
        }
        if (strtolower($this->type) == 'vehicle check on-call') {
            return 'Vehicle take out (On-call)';
        }
        if (strtolower($this->type) == 'return check') {
            return 'Vehicle return';
        }
        if (strtolower($this->type) == 'defect report' && strtolower($this->defect_report_type) == 'manual') {
            return 'Defect report (manual)';
        }
        if (strtolower($this->type) == 'defect report') {
            return 'Defect report';
        }
    }

    public function formattedCreatedAt()
    {
        return $this->created_at->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }

    public function formattedReportDatetime()
    {
        return $this->report_datetime->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }

    public function formattedUpdatedAt()
    {
        return $this->updated_at->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }

    public function duration_to_display()
    {
        $outputString = "";
        if ($this->check_duration != null) {
            $time = explode(":", $this->check_duration);
            if (isset($time[0]) && $time[0] != "00") {
                $outputString.=$time[0]." hours";
            }
            if (isset($time[1]) && $time[1] != "00") {
                $outputString.=$time[1]." mins";
            }
            if (isset($time[2]) && $time[2] != "00") {
                $outputString.=$time[2]." seconds";
            }
        }
        else{
            $outputString = "N/A";
        }
        
        return $outputString;
    }
}
