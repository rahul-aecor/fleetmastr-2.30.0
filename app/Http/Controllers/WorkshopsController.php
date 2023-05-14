<?php

namespace App\Http\Controllers;

use Mail;
use Auth;
use Hash;
use View;
use DB;
use Input;
use JavaScript;
use App\Models\Role;
use App\Models\User;
use App\Models\VehicleDivisions;
use App\Models\VehicleRegions;
use App\Http\Requests;
use App\Models\Company;
use App\Models\WorkshopCompany;
use App\Models\ColumnManagements;
use Illuminate\Http\Request;
use App\Models\UserVerification;
use App\Http\Controllers\Controller;
use App\Repositories\WorkshopRepository;
use App\Http\Requests\StoreCompanyRequest;
use App\Custom\Facades\GridEncoder;

class WorkshopsController extends Controller
{
    public $title= 'Workshops';

    public function __construct() {
        View::share ( 'title', $this->title );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rolesOptions = $this->getAllRoles();
        $companyOptions = $this->getAllCompanies();
        $lineManagerOptions = $this->getAllLineManagers();

        $column_management = ColumnManagements::where('user_id',auth()->user()->id )
        ->where('section','workshops')
        ->select('data')
        ->first();


        JavaScript::put([
            'column_management' => $column_management
        ]);

        return view('workshops.index')
            ->with('rolesList', $rolesOptions)
            ->with('companyList', $companyOptions)
            ->with('lineManagerList', $lineManagerOptions);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//        $allRegions = config('config-variables.vehicleRegions');
      //  $removedFirstElement = array_shift($allRegions);

        //$allRegions = array_values($allRegions);
        $user = new User();
        $user->email = $request->email;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->company_id = $request->company_id;
        $user->job_title = 'Workshop User';
        $user->mobile = $request->mobile;
        $user->landline = $request->landline;
        $user->address1 = $request->address1;
        $user->address2 = $request->address2;
        $user->town_city = $request->town_city;
        $user->postcode = $request->postcode;
        $user->enable_login = $request->enable_login;
        //$user->accessible_regions = $allRegions;
	$user->workshops_user_flag = 1;

        $token = str_random(30);
        $link = url('users/verification', [$token]);

        if($user->save()) {
            $user->roles()->sync(['12']);

            $userVerification = new UserVerification();
            $userVerification->user_id = $user->id;
            $userVerification->key = $token;
            $userVerification->save();
            $divisions = VehicleDivisions::all()->lists('id')->toArray();
            $regions = VehicleRegions::all()->lists('id')->toArray();
           if(env('IS_DIVISION_REGION_LINKED_IN_VEHICLE'))
            {
                $user->divisions()->sync($divisions);
            }

            if(!empty($regions))
            {
                $user->regions()->sync($regions);
            }

            $userName = $user->first_name;
            $emailAddress = $user->email;

            Mail::queue('emails.user_set_password', ['userName' => $userName, 'emailAddress' => $emailAddress, 'link' => $link], function ($message) use ($userName, $emailAddress, $token) {
                $message->to($emailAddress);
                $message->subject('fleetmastr - set your account password');
            });

            flash()->success(config('config-variables.flashMessages.dataSaved'));
        } else{
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }
        return redirect('workshops');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = $user->roles()->get();
        $givenRoles = $roles->pluck('id');
        $rolesOptions = $this->getAllRoles();
        $companyOptions = $this->getAllCompanies();
        $user->role_id = $givenRoles->all();
        $lineManagerOptions = $this->getAllLineManagers($id);
        $companyId = $user->company_id;

        $companyData = [];
        $company = Company::where('id',$companyId)->first()->toArray();


        $companyData['id'] = $company['id'];
        $companyData['name'] = $company['name'];

        if($user->workshops_user_flag == 1 || $user->workshops_user_flag == 2) {
            $companyOptions[$company['id']] = $company['name'];
        }

        return view('workshops.edit')
            ->with('user', $user)
            ->with('rolesList', $rolesOptions)
            ->with('companyList', $companyOptions)
            ->with('lineManagerList', $lineManagerOptions);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->mobile = $request->mobile;
        $user->enable_login = $request->enable_login;
        $user->landline = $request->landline;
        $user->address1 = $request->address1;
        $user->address2 = $request->address2;
        $user->town_city = $request->town_city;
        $user->postcode = $request->postcode;
        $user->company_id = $request->company_id;
        if($user->save()) {
            flash()->success(config('config-variables.flashMessages.dataSaved'));
        } else{
            flash()->error(config('config-variables.flashMessages.dataNotSaved'));
        }

        return redirect('workshops');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getAllRoles() {
        $returnArray = Role::where('name', '!=', 'Backend manager')->where('name', '!=', 'App version handling')->lists('name', 'id')->unique()->sort()->toArray();
        $value = "Super admin";
        $key = array_search($value, $returnArray);
        unset($returnArray[$key]);
        $superAdmin[$key] = $value;
        $returnArray = $superAdmin + $returnArray;
        return $returnArray;
    }

    public function getAllCompanies() {
        $allOtherCompanies = Company::where('user_type', 'Workshop')->orderBy('name')->lists('name', 'id')->unique()->toArray();
        // $lastTwo = Company::whereIn('name',['Aecor','Other'])->lists('name', 'id')->unique()->toArray();
        return ['' => ''] + $allOtherCompanies;
    }

    public function getAllLineManagers($id = null) {
        if (!is_null($id) && !empty($id)) {
            return ['' => ''] + User::select(
                \DB::raw("CONCAT(first_name,' ', last_name) AS full_name, id")
            )->where('id','!=',$id)->orderBy('first_name')->lists('full_name', 'id')->toArray();
        }
        return ['' => ''] + User::select(
            \DB::raw("CONCAT(first_name,' ', last_name) AS full_name, id")
        )->orderBy('first_name')->lists('full_name', 'id')->toArray();
    }

    public function anyData()
    {
       return GridEncoder::encodeRequestedData(new WorkshopRepository(), Input::all());
    }

    /**
     * Used To Disable User.
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function anyDisable($id)
    {
        if(Auth::id()==$id) {
            flash()->success(config('config-variables.flashMessages.noDeleteAccess'));
        } else {
            $user=User::find($id);
            $user->is_disabled = 1;
            if ($user->save()) {
                flash()->success(config('config-variables.flashMessages.userDisabled'));
            } else {
                flash()->success(config('config-variables.flashMessages.userNotDisabled'));
            }
        }
        return redirect('workshops');
    }

    /**
     * Used To Enable User.
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function anyEnable($id)
    {
        //$user=User::find($id);
        $user=User::withDisabled()->where('id', $id)->first();
        $user->is_disabled = 0;
        if ($user->save()) {
            flash()->success(config('config-variables.flashMessages.dataEnabled'));
        } else {
            flash()->success(config('config-variables.flashMessages.dataNotEnabled'));
        }
        return redirect('workshops');
    }

    /**
     * Resend invitation
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function resendInvitation(Request $request, $id)
    {
        $userId = $request->id;

        UserVerification::where('user_id', $userId)->delete();
        $user = User::find($userId);

        $userName = $user->first_name;
        $emailAddress = $user->email;
        $key = str_random(30);
        $link = url('users/verification', [$key]);

        $userVerification = new UserVerification();
        $userVerification->user_id = $user->id;
        $userVerification->key = $key;
        $userVerification->save();

        Mail::queue('emails.user_set_password', ['userName' => $userName, 'emailAddress' => $emailAddress, 'link' => $link], function ($message) use ($userName, $emailAddress, $key) {
            $message->to($emailAddress);
            $message->subject('fleetmastr - set your account password');
        });
        flash()->success('Invitation has been sent.');

        return redirect('/workshops');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addCompany(StoreCompanyRequest $request)
    {
        $company = new Company();
        $company->name = $request->name;
        $companyNameLength = strlen($request->name);

        if($companyNameLength >= 3) {
            $company->abbreviation = substr($request->name, 0, 3);
        } else {
            $company->abbreviation = $request->name;
        }

        $company->user_type = 'Workshop';
        if($company->save()){
            return $this->getAllCompaniesJson();
        }
    }


    public function getAllCompaniesJson() {
        $allOtherCompanies = Company::where('user_type', 'Workshop')->orderBy('name')->lists('name', 'id')->unique()->toArray();
        $returnJsonString = '['.'{"id":"","name":""},';
        foreach ($allOtherCompanies as $key => $value) {
            $returnJsonString = $returnJsonString . '{"id":"'.$key.'","name":"'.$value.'"},';
        }
        $returnJsonString = rtrim($returnJsonString,',') .']';

        return $returnJsonString;
    }

    //Check wether email find or not in ajax call
    public function checkEmail(Request $request) {
        if ($request->email !== null && !empty($request->email)) {
            if ($request->id) {
                $user = DB::table('users')->where('email', $request->email)->where('id', '!=', $request->id)->first();
            }
            else {
                $user = DB::table('users')->where('email', $request->email)->first();
            }
            if ($user) {
                return "false";
            }
        }
        return "true";
    }


    public function viewAllCompanies(Request $request) {
        $data = $request->all();
        $redirect = $data['redirect'];
        if($redirect == 'user') {
          return $this->getAllCompaniesJsonForUser();
        } else {
          return $this->getAllCompaniesJsonForWorkshop();
        }

    }

    public function updateCompanyName(Request $request){
        $id = Input::get('pk');
        $value = Input::get('value');
        $field = Input::get('name');

        $companyName = Company::find($id);
        $companyName->name = $value;
        if($companyName->save()) {
          if($field == 'user') {
            return $this->getAllCompaniesJsonForUserDropdown();
          } else {
            return $this->getAllCompaniesJson();
          }
        }
    }

    public function companyDelete(Request $request){
      $data = $request->all();
      $id = $data['id'];
      $redirect = $data['redirect'];

        if(Company::where('id', $id)->forcedelete()) {
          if($redirect == 'user') {
            return $this->getAllCompaniesJsonForUserDropdown();
          } else {
            return $this->getAllCompaniesJson();
          }
        }
    }

    public function getAllCompaniesJsonForUser() {
        $allOtherCompanies = Company::with('user_company', 'defect_history_company')->whereNotIn('name',['Aecor','Other'])->where('user_type', 'Other')->orderBy('name')->get()->unique();
        $lastTwo = Company::with('user_company', 'defect_history_company')->whereIn('name',['Aecor','Other'])->get()->unique();
        $returnJsonString = array();

        foreach ($allOtherCompanies as $key => $value) {
            array_push($returnJsonString, ['id'=>$value['id'], 'name'=>$value['name'], 'user_company'=>$value->user_company, 'defect_history_company'=>$value->defect_history_company]);
        }
        foreach ($lastTwo as $key => $value) {
            array_push($returnJsonString, ['id'=>$value['id'], 'name'=>$value['name'], 'user_company'=>$value->user_company, 'defect_history_company'=>$value->defect_history_company]);
        }

        return $returnJsonString;
    }

    public function getAllCompaniesJsonForWorkshop() {
        $allOtherCompanies = Company::with('user_company', 'defect_history_company')->where('user_type', 'Workshop')->orderBy('name')->get()->unique();
        $returnJsonString = array();

        foreach ($allOtherCompanies as $key => $value) {
            array_push($returnJsonString, ['id'=>$value['id'], 'name'=>$value['name'], 'user_company'=>$value->user_company, 'defect_history_company'=>$value->defect_history_company]);
        }

        return $returnJsonString;
    }

    public function getAllCompaniesJsonForUserDropdown() {
      $allOtherCompanies = Company::whereNotIn('name',['Aecor','Other'])->where('user_type', 'Other')->orderBy('name')->lists('name', 'id')->unique()->toArray();
      $lastTwo = Company::whereIn('name',['Aecor','Other'])->lists('name', 'id')->unique()->toArray();
      //$allCompanies = array_push($allOtherCompanies,$lastTwo);
      $returnJsonString = array();
      foreach ($allOtherCompanies as $key => $value) {
          array_push($returnJsonString, ['id'=>$key, 'text'=>$value]);
      }
      foreach ($lastTwo as $key => $value) {
          array_push($returnJsonString, ['id'=>$key, 'text'=>$value]);
      }

      return $returnJsonString;
    }
}
