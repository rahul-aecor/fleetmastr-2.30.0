<?php

namespace App\Models;

use Carbon\Carbon as Carbon;
use App\Scopes\UserDisabledScope;
use App\Models\MessageRecipient;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, SoftDeletes;
    

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "email",
        "username",
        "first_name",
        "last_name",
        "company_id",
        "job_title",
        "mobile",
        "landline",
        "engineer_id",
        "is_active",
        "is_lanes_account",
        "is_default_password",
        "imei",
        "line_manager",
        "field_manager_phone",
        "last_login",
        "fuel_card_personal_use",
        "fuel_card_issued",
        "user_division_id",
        "user_region_id",
        "user_locations_id",
        "accessible_divisions"
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_lanes_account' => 'boolean',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new UserDisabledScope);
    }

    /**
     * Get a new query builder that includes disabled Users.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function withDisabled()
    {
        return (new static)->newQueryWithoutScope(new UserDisabledScope);
    }

    /**
     * Get the company record associated with the user.
     */
    public function company()
    {
        return $this->hasOne('App\Models\Company','id','company_id');
    }

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role');
    }

    /**
     * The division that belong to the user.
     */
    public function division()
    {
        return $this->belongsTo('App\Models\UserDivision', 'user_division_id');
    }

    /**
     * The division that belong to the user.
     */
    public function userDivision()
    {
        return $this->belongsTo('App\Models\UserDivision', 'user_division_id');
    }

    /**
     * The divisions that belong to the user.
     */
    public function divisions()
    {
        return $this->belongsToMany('App\Models\VehicleDivisions', 'user_accessible_divisions', 'user_id', 'vehicle_division_id');
    }

    /**
     * The region that belong to the user.
     */
    public function region()
    {
        return $this->belongsTo('App\Models\UserRegion', 'user_region_id');
    }

    /**
     * The region that belong to the user.
     */
    public function userRegion()
    {
        return $this->belongsTo('App\Models\UserRegion', 'user_region_id');
    }

    /**
     * The divisions that belong to the user.
     */
    public function regions()
    {
        return $this->belongsToMany('App\Models\VehicleRegions', 'user_accessible_regions', 'user_id', 'vehicle_region_id');
    }

    /**
     * The divisions that belong to the user.
     */
    public function messageDivisions()
    {
        return $this->belongsToMany('App\Models\UserDivision', 'user_message_accessible_divisions', 'user_id', 'user_division_id');
    }


    /**
     * The divisions that belong to the user.
     */
    public function messageRegions()
    {
        return $this->belongsToMany('App\Models\UserRegion', 'user_message_accessible_regions', 'user_id', 'user_region_id');
    }

    /**
     * Verify user the has passed roles.
    */
    public function hasRole($roles){
        $userRoles = $this->roles; 
        foreach ($roles as $key => $role) {
            if ($userRoles->contains($role)) {
                return true;
            }
        }
        return false;
    }

    public function isSuperAdmin()
    {
        foreach ($this->roles()->get() as $key => $value) {
            if (strtolower($value->name) == "super admin") {
                return true;
            }
        }
        return false;
    }

    /* isDebugColumnVisibleToUser function was added in refrence with ticket #6708*/
    public function isDebugColumnVisibleToUser()
    {
        $permisibleEmails = config('config-variables.allowViewingColumnsForDebug');
        if (in_array( strtolower($this->email) ,$permisibleEmails )) {
            return true;
        }    
        return false;
    }

    public function isBackendManager()
    {
        return (strtolower($this->roles()->first()->name) == "backend manager")?true:false;
    }

    public function checkRole($roleName)
    {
        if(empty($roleName)) {
            return false;
        }

        return (strtolower($this->roles()->first()->name) == strtolower($roleName)) ? true : false;
    }

    public function isWorkshopManager()
    {
        return ($this->roles()->count() === 1 && strtolower($this->roles()->first()->name) == "workshop manager")?true:false;
    }

    public function isUserInformationOnly()
    {
        $roles = $this->roles()->get()->pluck('name')->toArray();
        return in_array("User information only", $roles);
    }

    public function isAppUser()
    {
        $roles = $this->roles()->get()->toArray();
        if (count($roles) == 1 && strtolower($roles[0]['name']) == "app access"){
            return true;
        }
        return false;
    }

    public function isHavingBespokeAccess()
    {
        $roles = $this->roles()->get()->pluck('id')->toArray();
        $appVersionHandlingId = Role::where('name','App version handling')->first()->id;
        if ( 
            (!in_array('1', $roles) && !in_array('8', $roles) && !in_array('14', $roles) && !in_array($appVersionHandlingId, $roles)) || 
            (!in_array('1', $roles) && !in_array('14', $roles) && !in_array($appVersionHandlingId, $roles) && in_array('8', $roles) && count($roles) > 1)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get the last_login timestamp.
     *
     * @param  string  $value
     * @return string
     */
    public function getLastLoginAttribute($value)
    {
        //return $value;
        if ($value && $value != '0000-00-00 00:00:00') {
            return Carbon::parse($value)->setTimezone(config('config-variables.format.displayTimezone'))->format("H:i:s d M Y");
        }
        else {
            return 'No login data recorded';
        }        
    }

    public function getEmailAttribute($value)
    {
        if(str_contains($value,'-imastr.com')){
            return $this->username;
        }
        return $value;
    }
    /**
     * Get the unread message count.
     *
     * * @return string
     */
    public function unreadMessageCount()
    {       
        return MessageRecipient::where(['user_id'=>$this->id])
                        ->where('status','!=','read')->get()->count();
    }

    public function isRoleAssigned($role)
    {
        foreach ($this->roles()->get() as $key => $value) {
            if (strtolower($value->name) == $role) {
                return true;
            }
        }
        return false;
    }

    public function isHavingRegionAccess($region)
    {
        $allRegions = \Auth::user()->regions->lists('id')->toArray();
        return in_array($region, $allRegions);
    }
}
