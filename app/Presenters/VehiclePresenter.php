<?php

namespace App\Presenters;

use Pingpong\Presenters\Presenter;
use Carbon\Carbon;

class VehiclePresenter extends Presenter
{
    public function label_class_for_status()
    {
        if (strtolower($this->status) == 'roadworthy' || strtolower($this->status) == 'roadworthy (with defects)') {
            return 'label-success';
        }
        else if (strtolower($this->status) == 'vor' || strtolower($this->status) == 'vor - accident damage' || strtolower($this->status) == 'vor - bodyshop' || strtolower($this->status) == 'vor - mot' || strtolower($this->status) == 'vor - service' || strtolower($this->status) == 'vor - bodybuilder' || strtolower($this->status) == 'vor - quarantined'  ) {
            return 'label-danger';
        }
        else {
            return 'label-warning';
        }
    }

    public function formattedCreatedAt()
    {
        return $this->created_at->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }

    public function formattedUpdatedAt()
    {
        return $this->updated_at->setTimezone(config('config-variables.displayTimezone'))->format(config('config-variables.displayTimeFormat'));
    }
}
