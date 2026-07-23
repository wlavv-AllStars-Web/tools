<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\Security\TrustedAccessPolicy;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function attemptLogin(Request $request): bool
    {
        $authenticated = $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
        if (! $authenticated) {
            return false;
        }
        $user = $this->guard()->user();
        if (app(TrustedAccessPolicy::class)->allows($user, $request->ip())) {
            return true;
        }
        Log::warning('Login blocked by trusted access policy.', ['user_id' => $user?->getAuthIdentifier(), 'ip' => $request->ip()]);
        $this->guard()->logout();

        return false;
    }
}
