<?php 
namespace App;
use Illuminate\Contracts\Auth\Guard; 
use Laravel\Socialite\Contracts\Factory as Socialite; 
use App\Repositories\UsersRepository; 
use Request; 

class AuthenticateUser {     

     private $socialite;
     private $auth;
     private $users;

     public function __construct(Socialite $socialite, Guard $auth, UsersRepository $users) {   
        $this->socialite = $socialite;
        $this->users = $users;
        $this->auth = $auth;
    }

    public function execute($hasCode, AuthenticateUserListener $listener) {
        if (! $hasCode) return $this->getAuthorizationFirst();
        // $user= $this->users->findByUserNameOrCreate($this->getGoogleUser());
        $user= $this->users->findByEmail($this->getGoogleUser());
        
        if ($user) {
            $this->auth->login($user);
            return $listener->userHasLoggedIn($user);
        } else {
            return $listener->userCannotLogIn();
        }
    }

    private function getAuthorizationFirst() {        
        return $this->socialite->driver('google')->redirect();
    }

    private function getGoogleUser() {
        return $this->socialite->driver('google')->user();
    }
}