<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use PhpSpec\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SyncController extends APIController
{
    protected $dependentActions = [
        'surveys' => [
            'store' => true
        ]
    ];
    /**
     * Process the sync request.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \Log::info('sync request starts');
        \Log::info($request->all());
        \Log::info($request->get('data'));

        // $arr = json_decode('[{"action":"","payload":"{\"user_email\":\"rstenson@aecordigital.com\",\"imei\":\"000000000000000\"}","entity":"startup"},{"action":"questions","payload":"","entity":"survey"},{"action":"all","payload":"","entity":"vehicle"}]');
        // echo "<pre>";print_r($arr);echo "</pre>";exit;

        if (! $request->has('data')) {
            throw new BadRequestHttpException('Data not present.');
        }
        
        $dispatcher = app('Dingo\Api\Dispatcher');
        $prefix = config('api.prefix') . "/" . config('api.version');
        $dispatcher->setPrefix($prefix);

        $all_sync_data = json_decode($request->get('data'), true);
        \Log::info('$all_sync_data ' . gettype($all_sync_data));

        if(gettype($all_sync_data) == 'string') {
            \Log::info('all_sync_data is string');
            $all_sync_data = json_decode($all_sync_data, true);
        }
        
        $results = [];
        $work_id = "";
        $user = [];

        foreach ($all_sync_data as $key => $data) {

            $payload = [];
            if (isset($data['payload'])) {
                $payload = json_decode($data['payload'], true);
            }
            $payload['is_sync_call'] = true;
            $result = [];
            $url = "";
            if(isset($data['entity']) && isset($data['action'])) {
                $url = $data['entity'] . "/" . $data['action'];
            } else if(isset($data['action'])) {
                $url = $data['action'];
            }
            // $url = $data['entity'] . (isset($data['action'] && $data['action']) ? "/" . $data['action'] : "");

            \Log::info('Syncing with route ' . $url);
            try {
                //$result = $dispatcher->with($payload)->post($url);
                // startup - return the user array in the response
                /*if ($data['entity'] == 'startup') {
                     //$result = $dispatcher->with($payload)->post($url);
                    //$user = $result;
                    //$resp['data']['user'] = $user;
                    $dispResp = $dispatcher->raw()->with($payload)->post($url);
                    $result['data'] = json_decode($dispResp->getContent());
                    \Log::info('Received startup response');                      
                }
                else{
                    $respData = $dispatcher->with($payload)->post($url);
                    $result['data'] = $respData;                    
                }*/
                $dispResp = $dispatcher->raw()->with($payload)->post($url);
                $result['data'] = json_decode($dispResp->getContent());
                $result['status_code'] = 200;   
                $result['message'] = 'Success';   
                $result['action'] = $data['action']; 
                \Log::info("try execute success");
            }
            catch(\Dingo\Api\Exception\ValidationHttpException $e) {
                $result['status_code'] = $e->getStatusCode();
                $result['message'] = 'Failure';
                $result['errors'] = $e->getErrors();   
                $result['action'] = $data['action']; 
            }
            catch(\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $result['status_code'] = $e->getStatusCode();
                $result['message'] = 'Failure';
                $result['errors'] = $e->getMessage();   
                $result['action'] = $data['action']; 
            }
            catch(\Exception $e) {
                \Log::info($e);
                $result['status_code'] = 500;
                $result['message'] = 'Failure';
                $result['errors'] = 'Error while syncing.';   
                $result['action'] = $data['action']; 
            }
            // \Log::info('sync result');
            // \Log::info($result['data']);
            // \Log::info('print result');
            
            //$all_sync_data[$key]['response'] = $result;
            array_push($results, $result);

        }
        // return $all_sync_data;
        
        /*$resp = ["status_code" => 200];
        
        if (count($user)) {
            $resp['data']['user'] = $user;
        }
        return $this->response->array($resp);*/
        //\Log::info($results);
        return $this->response->array($results);
        
    }

    protected function isDependent($entity, $action) 
    {
        return isset($this->dependentActions[$entity][$action]);
    }
}
