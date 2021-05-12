<?php

namespace App\Http\Controllers\v1;


use App\Http\Controllers\Controller;
use App\Helpers\HelperFunctions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Claims\Expiration;
use Tymon\JWTAuth\Claims\IssuedAt;
use Tymon\JWTAuth\Claims\Issuer;
use Tymon\JWTAuth\Claims\JwtId;
use Tymon\JWTAuth\Claims\NotBefore;
use Tymon\JWTAuth\Claims\Subject;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Validator;

class UserManagementController extends Controller
{

    function __construct()
    {

        $this->HelperFunctions = new HelperFunctions();

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This Method Use for login user to system
     *
     */
    public function loginUser(Request $request)
    {


        $validator = Validator::make($request->all(), [

            'username' => 'required|string',
            'password' => 'required|string'

        ]);

        /** Return Response if Validatior Fails */

        if ($validator->fails()) {

            $validationErrors = $validator->errors()->getMessages();

            $returnData = array(

                'message' => $validationErrors,

            );

            return $this->HelperFunctions->returnData($returnData, false, "Something Went Wrong (Request Params Failed)", 400);

        }

        $user_details = DB::select('select * from employee where username = ? and password = ?', [$request->username,md5($request->password)]);

        if($user_details==[]){
            return $this->HelperFunctions->returnData(array(), false, "Invalid Credentials", 401);
        }


        $data = [
            'id' => $user_details[0]->id,
            'username' => $user_details[0]->username,
            'password' => $user_details[0]->password,
            'role' => $user_details[0]->role_id == 1 ? "Admin" : "Employee",
            'registration_date_unix' => $user_details[0]->registration_date,
            'registration_date' => date('d-M-Y', $user_details[0]->registration_date),
            'userRole' => $user_details[0]->role_id,
            'iss' => new Issuer('AP'),
            'iat' => new IssuedAt(Carbon::now('UTC')),
            'exp' => new Expiration(Carbon::now('UTC')->addDays(1)),
            'nbf' => new NotBefore(Carbon::now('UTC')),
            'sub' => new Subject('AP'),
            'jti' => new JwtId('AP'),
        ];


        $customClaims = JWTFactory::customClaims($data);
        $payload = JWTFactory::make($data);
        $token = JWTAuth::encode($payload);

//                            JWT Token Start End

        session(['session_token' => $token->get()]);

        return $this->HelperFunctions->returnData(array("user_type"=>$user_details[0]->role_id), true, "Successfully logged in", 200);


    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This Method Use for logout user from system
     *
     */
    public function logoutUser(){

        session(['session_token' =>null]);

        return $this->HelperFunctions->returnData(array(), true, "Successfully logout", 200);


    }
}
