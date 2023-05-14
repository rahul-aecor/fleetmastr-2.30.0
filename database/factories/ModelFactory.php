<?php
use Carbon\Carbon as Carbon;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
   //  $fname = $faker->firstName;
   //  $lname = $faker->lastName;
   //  $name = $fname . " " . $lname;
   //  return [
   //     'email' => $faker->safeEmail,
   //     'password' => bcrypt('admin123'),
   //     'first_name' => $fname,
   //     'last_name' => $lname,
   //     'company_id' => $faker->numberBetween(1,17),
   //     'region' => $faker->randomElement(['Central','East','South East','South West','West']),
   //     'mobile' => '07890123'.$faker->randomDigit().$faker->randomDigit().$faker->randomDigit(),
   //     'base_location' => $faker->city,
   //     'engineer_id' => 'ENG'.$faker->randomDigit().$faker->randomDigit().$faker->randomDigit().$faker->randomDigit(),
   //     'is_active' => true,
   //     'is_lanes_account' => true,
   //     'imei' => $faker->randomNumber(7).$faker->randomNumber(8),
   //     'field_manager_phone' => '07890123'.$faker->randomDigit().$faker->randomDigit().$faker->randomDigit()
   // ];
    return [
       'email' => '',
       'password' => '',
       'first_name' => '',
       'last_name' => '',
       'company_id' => 1,
       'is_active' => true,
       'is_lanes_account' => true,
        "created_at" => (new Carbon('now'))->toDateTimeString(),
        "updated_at" => (new Carbon('now'))->toDateTimeString()
   ];
});

$factory->define(App\Models\Vehicle::class, function () {
    return [
        "registration" => "",
        "vehicle_type_id" => "",
        "status" => "",
        "dt_added_to_fleet" => "",
        "last_odometer_reading" => null,
        "odometer_reading_unit" => "km",
        "dt_registration" => "",
        "chassis_number" => "",
        "vehicle_location_id" => "",
        "vehicle_repair_location_id" => "",
        "vehicle_region" => "",
        "dt_repair_expiry" => "",
        "dt_mot_expiry" => "",
        "masternaut" => "",
        "created_by" => "3",
        "updated_by" => "3",
        "created_at" => (new Carbon('now'))->toDateTimeString(),
        "updated_at" => (new Carbon('now'))->toDateTimeString()
    ];
});