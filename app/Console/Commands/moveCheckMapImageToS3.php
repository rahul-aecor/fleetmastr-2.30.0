<?php

namespace App\Console\Commands;

use App\Models\Check;
use Illuminate\Console\Command;

class moveCheckMapImageToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'move:checkMapImage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To move check map images to s3';

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
        //$checks = Check::select('id','status','type')->where('location', '!=', 'NULL')
        $checks = Check::where('location', '!=', 'NULL')
                        ->where('type', 'Vehicle Check')
                        ->where('location', '!=', '0.000000,0.000000')
                        ->whereDate('updated_at', '>', '2019-04-01')
                        ->get(); //->toArray();
//print_r($checks);exit;
        foreach ($checks as $key => $check) {
            if(!$check->hasMedia('checkMapImage')) {
                $locationArray = explode(",", $check->location);
                $checkMapImageUrl = "https://maps.googleapis.com/maps/api/staticmap?center:" .$locationArray[0]. ',' .$locationArray[1]."&format=png&zoom=15&scale=2&size=1000x250&maptype=roadmap&markers=color:red%7C" .$locationArray[0] . ',' .$locationArray[1]. "&key=".config('config-variables.google_map_key');

                $desFolder = public_path('img/checks/gmap/');
                if (!file_exists($desFolder)) {
                    mkdir($desFolder, 0777, true);
                }
                $imageName = 'check_map_image_'.$check->id.'.png';
                $imagePath = $desFolder.$imageName;            
                file_put_contents($imagePath, file_get_contents($checkMapImageUrl));

                $check = Check::find($check->id);
                $checkMapImage = $check->addMedia($imagePath)
                                        ->toCollectionOnDisk('checkMapImage', 'S3_uploads');
                $this->info("Check $check->id map image has been moved to S3");
            }
        } 
        sleep(1);
    }
}
