<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Mail;
use App\Models\User;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected $subject = 'fleetmastr - reset your account password';

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Display the password reset view for the given token.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function getReset($token = null)
    {
        if (is_null($token)) {
            throw new NotFoundHttpException;
        }
        $password_reset = \DB::table('password_resets')->where('token', $token)->first();
        if(empty($password_reset)){
            // throw new NotFoundHttpException;
            return redirect('/login')->withErrors(['email' => 'Your reset password link has expired.']);
        }
        $email = $password_reset->email;
        return view('auth.reset')->with('token', $token)->with('email', $email);
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        // check if it is a lanes email
        $email = $request->get('email');
        $email = str_replace('@'.env('BRAND_NAME').'-imastr.com','',$email);
        $user = User::select('is_lanes_account','is_disabled','is_verified')->where('email',$email)->first();

         if (!is_null($user)){
            if($user->is_verified != 1){
                $msg = "Your account is inactive. Please verify your account by clicking on the link sent you in email.";
                return redirect()->back()->withErrors(['email' => $msg]);
            }
            if($user->is_lanes_account || $user->is_disabled) {
                $msg = "The password connected with your Lanes Group email address cannot be reset on this platform. Please contact support@lanes-i.com for assistance.";
                return redirect()->back()->withErrors(['email' => $msg]);
            }
        }else{
             $msg = "This email does not match our records.";
             return redirect()->back()->withErrors(['email' => $msg]);
        }

        $response = Password::sendResetLink($request->only('email'), function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return redirect()->back()->with('status', "An email has been sent to you with a password reset link");

            case Password::INVALID_USER:
                return redirect()->back()->withErrors(['email' => trans($response)]);
        }
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postReset(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ],[
            'confirmed' => 'Oops! Your passwords do not match'
        ]
        );

        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

         $email = $request->get('email');
        $user = User::select('is_lanes_account','is_disabled')->where('email',$email)->first();

        if (!is_null($user)){
            if($user->is_lanes_account || $user->is_disabled) {
                $msg = "The password connected with your Lanes Group email address cannot be reset on this platform. Please contact support@lanes-i.com for assistance.";
                return redirect()->back()->withErrors(['email' => $msg]);
            }
        }else{
             $msg = "This email does not match our records.";
                return redirect()->back()->withErrors(['email' => $msg]);
        }

        $response = Password::reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                $email = $request->get('email');
                $user = User::where('email',$email)->first();
                $data = array('email'=>$email, 'first_name'=>$user->first_name);
                $result = Mail::send('emails.success_reset', ['data' => $data], function ($message) use ($data) {
                                    $message->to($data['email']);
                                    $message->subject('fleetmastr - your password has been reset');
                                });
                if($user->isAppUser()) {
                    return redirect('/auth/successreset')->with('message', 'Your password has successfully been reset.');
                } else {
                    return redirect('/login')->with('message', 'Your password has successfully been reset.');
                }

            default:
                return redirect()->back()
                            ->withInput($request->only('email'))
                            ->withErrors(['email' => trans($response)]);
        }
    }

    public function isEmailExists(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if(!$user) {
            return 'false';
        } else {
            return 'true';
        }
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = bcrypt($password);

        $user->save();

        // Auth::login($user);
    } 

    protected function successReset()
    {
        return view('auth.successreset');
    }       
}
