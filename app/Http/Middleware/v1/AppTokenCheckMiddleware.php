<?php

namespace App\Http\Middleware\v1;

use Closure;

class AppTokenCheckMiddleware
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

        /** @var $allHeaders Get All Headers */

        $allHeaders = $request->headers->all();

        /** Validate App Token in Header */

        if( isset( $allHeaders['app-token'] ) ){

            /** If token mismatch return */

            $app_token = $allHeaders['app-token'][0];

            if( strcmp( $app_token, '$*P?vm!QT?_sX=hv+jAsFgxmc2EFB!') == 0 ){

                return $next($request);

            }else{

                /** Return Error */

                return redirect('api/v1/login-error');
            }

        }else{

            /** Return Error */

            return redirect('api/v1/login-error');

        }

    }
}
