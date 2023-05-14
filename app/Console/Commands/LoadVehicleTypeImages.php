<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VehicleType;

class LoadVehicleTypeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicleprofile:loadimages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to load vehicle Type images';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $frontFile = "http://sunny-fleetmastr.dev.aecortech.com/img/types/Front.png";
        $backFile = "http://sunny-fleetmastr.dev.aecortech.com/img/types/Back.png";
        $leftFile = "http://sunny-fleetmastr.dev.aecortech.com/img/types/Left.png";
        $rightFile = "http://sunny-fleetmastr.dev.aecortech.com/img/types/Right.jpg";

        // $vehicleTypes = VehicleType::whereIn('id', [1,2])->get();
        $vehicleTypes = VehicleType::all();
        foreach ($vehicleTypes as $vehicleType) {
            $vehicleType->clearMediaCollection('frontview');
            $vehicleType->addMediaFromUrl($frontFile)
                        ->setFileName($vehicleType->id."_front")
                        ->withCustomProperties(['mime-type' => 'image/png'])
                        ->preservingOriginal()
                        ->toCollection('frontview');

            $vehicleType->clearMediaCollection('backview');
            $vehicleType->addMediaFromUrl($backFile)
                        ->setFileName($vehicleType->id."_back")
                        ->withCustomProperties(['mime-type' => 'image/png'])
                        ->preservingOriginal()
                        ->toCollection('backview');

            $vehicleType->clearMediaCollection('leftview');
            $vehicleType->addMediaFromUrl($leftFile)
                        ->setFileName($vehicleType->id."_left")
                        ->withCustomProperties(['mime-type' => 'image/png'])
                        ->preservingOriginal()
                        ->toCollection('leftview');

            $vehicleType->clearMediaCollection('rightview');
            $vehicleType->addMediaFromUrl($rightFile)
                        ->setFileName($vehicleType->id."_right")
                        ->withCustomProperties(['mime-type' => 'image/jpg'])
                        ->preservingOriginal()
                        ->toCollection('rightview');

        }
    }
}
