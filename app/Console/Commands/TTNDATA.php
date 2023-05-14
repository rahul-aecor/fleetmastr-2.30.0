<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TelematicsJourneys;
use Carbon\Carbon as Carbon;
use App\Models\Vehicle;
use App\Services\TelematicsService;
use App\Repositories\TelematicsJourneyDetailsRepository;
use App\Repositories\TelematicsJourneysRepository;
use GuzzleHttp\Client;


class TTNDATA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telematics:TTNDATA';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    private function callJourneysApi($url)
    {
        $from1 = Carbon::now()->subMinutes(2)->format('Y-m-d');
        $from2 = Carbon::now()->subMinutes(2)->format('H:i:s');
        $from = $from1.'T'.$from2;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api-uk.nextgen.teletracnavman.net/v1/trips?vehicleId=16503&from=2022-10-13T00:00:09&event_types=IOR,VPM_IT,SPEED,GEOFENCE,VPM_HC,VPM_IM,VPM_HB,VPM_OR,VPM_EA,VPM_EOP,VPM_ECT,VPM_EOT,ALARM,PRETRIP,FORM,ALERT,PTO,CAMERA,DRIVER,MASS,FATIGUE,GPIO&embed=meters,events');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Token token="'.env('TELETRAC_KEY').'"' ,
                    'Content-Type: application/json'
                    ));
        $output = curl_exec($ch);
        $resp = json_decode($output,true);
        curl_close($ch);
        return $resp;
/*                    foreach($resp as $test){
                        print_r($test);
                    }
*/

    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$this->callJourneysApi('abc');exit;
        //$srcFilePath = base_path("storage") . '/logs/telematics/trackm8rawdata-' . Carbon::yesterday()->format('d-m-Y') . ".log";
        //$srcFilePath = '/home/hitesha/ttndatachunk.txt';
        //$srcFilePath = '/home/hitesha/ttndatawithouttype.txt';
        //$srcFilePath = '/home/hitesha/teletrackrawdata.log';
        //$srcFilePath = '/home/hitesha/teletrackrawdata_23-01-09.log';
        $srcFilePath = '/home/hitesha/teletrackrawdata_23-01-10.log';

                print_r("===================================================\n");
                $readFromFile = fopen($srcFilePath, "r");
                //$vrn = 'SH69XVB';
                //$vrn = 'BK22 WZE';
                $vrn = 'CN69 KUR';
                $writeToFile = fopen("/home/hitesha/".$vrn."_teletrack.txt", "w") or die("Unable to open file!");
//myfile2 = fopen("/home/hitesha/ttndatawithoutresult.txt", "w") or die("Unable to open file!");
                while(!feof($readFromFile)) {
                    $line = fgets($readFromFile);
                    //$dataString = trim(mb_substr($line, 42));
                    //$dataString = trim(mb_substr($dataString, 0, strlen($dataString)-5));
                    $data = json_decode($line);
                    if (!empty($data)){
                        if (isset($data->vehicle) && $data->vehicle->registration == $vrn) {
                            fwrite($writeToFile, $line);
                            //$txt = "\n";
                        }
                        else{
                            //fwrite($myfile2, $line);
                            //$txt = "\n";
                        }
                        //print_r("\n");
                    }
                }
                fclose($readFromFile);
                fclose($writeToFile);
                //fclose($myfile2);
                print_r("===================================================\n\n\n");
    }
}
