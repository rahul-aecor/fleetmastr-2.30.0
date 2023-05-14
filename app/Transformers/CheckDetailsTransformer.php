<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Check;
use App\Models\User;
use App\Traits\ProcessCheckJson;

class CheckDetailsTransformer extends TransformerAbstract {

    use ProcessCheckJson;

    public function transform(Check $check)
    {
        $user = User::find($check->created_by);
        $odometer_reading = (empty($check->odometer_reading))?$check->vehicle->last_odometer_reading:$check->odometer_reading;
        return [
                'history'=> [
                    'id' => $check->id,
                    'status'=> $check->status,
                    // 'json' => $this->recreateCheckJson($check),
                    'json' => $check->json,
                    'check_date' => $check->report_datetime->setTimezone(config('config-variables.displayTimezone'))->format('H:i d M Y'),
                    'odometer' => $odometer_reading,
                    'submitted_by_email' => $user->email,
                    'submitted_by_name' => $user->first_name . " " . $user->last_name
                ]
            ];
    }
}
?>