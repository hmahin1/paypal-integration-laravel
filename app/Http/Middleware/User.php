<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UserToken;

class User

{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->auth_token){
            $token = $request->auth_token;   
        
        }else{
            $token = $request->header('auth_token') || $request->auth_token;   
        
        }
        if($user_token = UserToken::where([['user_token',$token],['revoke_or_status', 'active']])->with('user')->first())
        {
            if($user_token->user->status == 'active'){
                $request->user = $user_token->user;
                return $next($request);     
            }
            else{
                return response()->json(['status' => false,'code' => 400,'error'=>'User is inactive']); 
            }            
        }
        else
            return response()->json(['status' => false,'code' => 400,'error'=>'Not authorize']); 
    }
}
