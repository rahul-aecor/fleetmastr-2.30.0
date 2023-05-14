<?php
namespace App\Console\Commands\WebFleet;

use App\Models\TelematicsJourneyDetails;
use Illuminate\Console\Command;
use App\Custom\Client\GoogleMap;

class UpdateJourneyDetailAdress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webfleet:updateJourneyDetailAdress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update address of journey detail which is empty.';

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
        $googleClient = new GoogleMap();

        $journeyDetails = TelematicsJourneyDetails::where(function($qry) {
                                                    $qry->whereNull('street')
                                                        ->orWhereNull('town')
                                                        ->orWhereNull('post_code')
                                                        ->orWhere('street', '')
                                                        ->orWhere('town', '')
                                                        ->orWhere('post_code', '');
                                                })
                                                ->get();

        foreach($journeyDetails as $journey) {
            $lat = $journey->lat;
            $lon = $journey->lon;
            $address = $googleClient->getAddressFromll($lat,$lon);
            $journey->street = $address['street'];
            $journey->town = $address['town'];
            $journey->post_code = $address['postal_code'];
            $journey->save();
        }
    }
}
