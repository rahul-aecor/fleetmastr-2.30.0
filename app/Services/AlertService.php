<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Alerts;
use App\Models\Vehicle;
use App\Repositories\AlertRepository;

class AlertService
{   

    public function __construct()
    {
    }

	public function alertData($request)
	{
        $alertRepository = new AlertRepository($request);
        $alertData = $alertRepository->alertData();
        return $alertData;
	}

    public function storeTestAlert($data)
    {
        $alertRepository = new AlertRepository($data);
        $testAlert = $alertRepository->storeTestAlert($data);
        return $testAlert;
    }
}