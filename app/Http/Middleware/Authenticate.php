<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Closure;
use Auth;

use App\Models\User;


class Authenticate extends Middleware
{

    protected function redirectTo(Request $request): ?string
    {        
        return $request->expectsJson() ? null : route('login');
    }    
    
    public function handle($request, Closure $next, ...$guards){        
        
        if( is_null(auth()->user())) return $this->auth->authenticate();
        
        $ids[1]  = (object)[ 'asm' => 0,   'asd' => 35,      'name' => 'Stock entry' ];
        $ids[2]  = (object)[ 'asm' => 2,   'asd' => 2,       'name' => 'Cedric' ];
        $ids[43] = (object)[ 'asm' => 43,  'asd' => 1,       'name' => 'Bruno Fernandes' ];
        $ids[59] = (object)[ 'asm' => 59,  'asd' => 59,       'name' => 'Margarida' ];
        $ids[62] = (object)[ 'asm' => 62,  'asd' => 62,       'name' => 'Ricardo' ];
        $ids[67] = (object)[ 'asm' => 67,  'asd' => 67,       'name' => 'Patricia' ];
        $ids[75] = (object)[ 'asm' => 75,  'asd' => 75,      'name' => 'Helio' ];
        $ids[78] = (object)[ 'asm' => 78,  'asd' => 78,      'name' => 'Pierre' ];
        $ids[81] = (object)[ 'asm' => 81,  'asd' => 81,      'name' => 'Sofia' ];
        $ids[85] = (object)[ 'asm' => 103, 'asd' => 103,      'name' => 'Rosario' ];
        $ids[88] = (object)[ 'asm' => 88,  'asd' => 42,      'name' => 'Sandra' ];
        $ids[92] = (object)[ 'asm' => 92,  'asd' => 38,      'name' => 'Daniel' ];
        $ids[94] = (object)[ 'asm' => 9,   'asd' => 9,       'name' => 'Luduvina' ];
        $ids[95] = (object)[ 'asm' => 95,  'asd' => 46,      'name' => 'Paulo' ];
        $ids[98] = (object)[ 'asm' => 0,   'asd' => 0,       'name' => 'Joao' ];
        $ids[99] = (object)[ 'asm' => 63,  'asd' => 16,      'name' => 'Dashboard' ];
        $ids[100]= (object)[ 'asm' => 98,  'asd' => 0,       'name' => 'Rodrigo' ];
        $ids[101]= (object)[ 'asm' => 97,  'asd' => 42,      'name' => 'Sandra' ];
        $ids[102]= (object)[ 'asm' => 100, 'asd' => 0,       'name' => 'Rita' ];
        $ids[103]= (object)[ 'asm' => 99,  'asd' => 0,       'name' => 'Bruno Bogalhas' ];
        $ids[104]= (object)[ 'asm' => 0,   'asd' => 0,       'name' => 'Joao' ];
        $ids[105]= (object)[ 'asm' => 102, 'asd' => 44,      'name' => 'Tania' ];
        $ids[106]= (object)[ 'asm' => 0,   'asd' => 0,       'name' => 'Joao Silva' ];
        $ids[107]= (object)[ 'asm' => 104, 'asd' => 0,       'name' => 'Helder' ];
        $ids[108]= (object)[ 'asm' => 0,   'asd' => 45,      'name' => 'Danilo' ];
        $ids[109]= (object)[ 'asm' => 0,   'asd' => 0,       'name' => 'Angela' ];
        $ids[110]= (object)[ 'asm' => 0,   'asd' => 0,       'name' => 'Lorenzo' ];
        $ids[111]= (object)[ 'asm' => 0,   'asd' => 0,       'name' => 'José' ];

        Config::set(['token' => User::getTokens( $ids[auth()->user()->id]->asm )]);
        Config::set(['tokenASD' => User::getTokensASD( $ids[auth()->user()->id]->asd )]);
        
        return $next($request);
    }

}
