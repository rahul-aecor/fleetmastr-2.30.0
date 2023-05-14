<?php

namespace App\Presenters;

use Pingpong\Presenters\Presenter;
use Carbon\Carbon;

class IncidentHistoryPresenter extends Presenter
{
    /**
     * Returns formatted timestamp field with adjusted timezone
     * 
     * @return string
     */
    public function formattedCreatedAt()
    {
        return $this->created_at->setTimezone(config('config-variables.displayTimezone'));
    }

    public function formattedUpdatedAt()
    {
        return $this->updated_at->setTimezone(config('config-variables.displayTimezone'));
    }
}
