<?php

namespace App\Console\Commands;

use Mail;
use Carbon\Carbon;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use App\Models\VehicleMaintenanceNotification;

class VehicleMaintenanceNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicle:maintenanceNotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send vehicle maintenance notification emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $events = config('config-variables.eventRemindersNotifications');
        foreach ($events as $event => $values) {
            $interval = \DateInterval::createFromDateString($values['interval']);
            $date = Carbon::today()->add($interval)->subDay()->format('Y-m-d');
            
            $vehicles = Vehicle::whereNotNull('nominated_driver')
                                ->whereNotNull($values['column'])
                                ->where($values['column'], '=', $date)
                                ->get();

            $eventCaption = $values['caption'];
            $eventColumn = $values['column'];
            $eventMessage = $values['message'];

            foreach ($vehicles as $vehicle) {
                $driver = $vehicle->nominatedDriver;
                $notification = VehicleMaintenanceNotification::where('user_id', $driver->id)
                                    ->where('event_type', $values['event'])
                                    ->where('is_enabled', true)
                                    ->first();

                $vehicleLink = url("/vehicles/{$vehicle['id']}");
                if($notification) {
                    $registration = $vehicle->registration;
                    $email = $driver->email;
                    $dueDt = Carbon::parse($vehicle->$eventColumn)->format('jS F Y');

                    // Sending main notification to the nominated driver for the vehicle
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        Mail::queue('emails.vehicle_maintenance_notification', ['eventName' => $values['event'], 'userName' => $driver->first_name, 'event' => $eventCaption, 'registration' => $registration, 'dueDt' => $dueDt, 'eventMessage' => $eventMessage, 'vehicleLink' => $vehicleLink], function ($message) use ($email, $driver, &$link, $registration, $vehicleLink) {
                            $message->to($email, $driver->first_name, $link, $registration, $vehicleLink);
                            $message->subject('fleetmastr - vehicle maintenance notification '.$registration);
                        });
                    }
                }
            }
        }

    }
}
