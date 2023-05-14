<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Check;

class DeleteDuplicateChecksEntry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:duplicate-checks-entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete duplicate checks';

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
        $duplicateApiIds = Check::whereNotNull('apiId')
							->groupBy('apiId')
							->havingRaw('count(id) > 1')
							->get(['apiId'])
							->pluck('apiId');

		$vehicleChecks = Check::whereIn('apiId', $duplicateApiIds)->orderBy('apiId')->get(['id', 'apiId']);

        $apiId = '';
		foreach($vehicleChecks as $key => $check) {
            if($apiId == '' || $apiId != $check->apiId) {
                $apiId = $check->apiId;
				continue;
			}
            $apiId = $check->apiId;
            \Log::info('**** DELETING DUPLICATE ENTRIES FOR APIID: '.$check->apiId. ' AND CHECK ID: '.$check->id);
			$check->delete();
		}
    }
}
