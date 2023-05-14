<?php

namespace App\Presenters;

use Pingpong\Presenters\Presenter;
use Carbon\Carbon;

class IncidentPresenter extends Presenter
{ 
    public function formattedCreatedAt()
    {
        return $this->created_at->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }

    public function formattedUpdatedAt()
    {
        return $this->updated_at->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }

    public function label_class_for_status()
    {        
        if (strtolower($this->status) == 'reported') {
            return 'label-danger';
        }
        if (strtolower($this->status) == 'under investigation' || strtolower($this->status) == 'allocated') {
            return 'label-warning';
        }
        if (strtolower($this->status) == 'closed') {
            return 'label-success';
        }
        return 'label-status-default';
    }
}
