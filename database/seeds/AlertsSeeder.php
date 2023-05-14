<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Alerts;

class AlertsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql')->table('alerts')->insert([
             [
                "name" => "First notification of loss",
                "slug" => "first_notification_of_loss",
                "description" => "description",
                "severity" => "critical",
                "type" => "fnol",
                "source" => "telematics",
                "is_active" => 1,
                "is_notification_enabled" => 1,
                "created_by" => env('SYSTEM_USER_ID'),
                "updated_by" => env('SYSTEM_USER_ID'),
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s'),

            ],
            [
                "name" => "Diagnostic trouble code",
                "slug" => "diagnostic_trouble_code",
                "description" => "description",
                "severity" => "high",
                "type" => "dtc",
                "source" => "telematics",
                "is_active" => 0,
                "is_notification_enabled" => 1,
                "created_by" => env('SYSTEM_USER_ID'),
                "updated_by" => env('SYSTEM_USER_ID'),
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s'),

            ],
            [
                "name" => "Vehicle check missing",
                "slug" => "vehicle_check_incomplete",
                "description" => "This alert notification triggers when a vehicle is moving (notified via telematics data) and an accompanying vehicle check has not been completed for that vehicle.",
                "severity" => "medium",
                "type" => "trigger",
                "source" => "system",
                "is_active" => 1,
                "is_notification_enabled" => 1,
                "created_by" => env('SYSTEM_USER_ID'),
                "updated_by" => env('SYSTEM_USER_ID'),
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s'),

            ],
        ]);
    }
}
