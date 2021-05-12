<?php

namespace App\Http\Middleware\v1;

use App\Helpers\HelperFunctions;
use App\Helpers\JwtDecoderHelper;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginStatusCheckMiddleware
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

        $allHeaders = $request->headers->all();

        if( isset( $allHeaders['app-token'] ) && isset( $allHeaders['session-token'] )){

            $token=$allHeaders['session-token'][0];
            $HelperFunctions=new HelperFunctions();

            try {
                JWTAuth::setToken($token); //<-- set token and check
                if (!$claim = JWTAuth::getPayload()) {

                    $HelperFunctions->returnData(array(), false, "USER NOT FOUND", 401);

                }
            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {


                $HelperFunctions->returnData(array(), false, "Session Expired", 401);


            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

                $HelperFunctions->returnData(array(), false, "TOKEN INVALID", 401);

            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

                $HelperFunctions->returnData(array(), false, "TOKEN ABSENT", 401);

            }

            session(['session_token' => $token]);
            return $next($request);

        }else{

            /** Return Login Error */


            return redirect('api/v1/login-invalid');

        }


    }
}
