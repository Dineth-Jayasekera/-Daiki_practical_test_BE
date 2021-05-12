<?php

namespace App\Http\Controllers\v1;

use App\Helpers\HelperFunctions;
use App\Helpers\JwtDecoderHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class employeeManagementController extends Controller
{

    function __construct()
    {

        $this->HelperFunctions = new HelperFunctions();

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This method use for save employee
     *
     */
    public function saveEmployee(Request $request)
    {


        $validator = Validator::make($request->all(), [

            'name' => 'required|string',
            'contact_number' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',

        ]);

        /** Return Response if Validatior Fails */

        if ($validator->fails()) {

            $validationErrors = $validator->errors()->getMessages();

            $returnData = array(

                'message' => $validationErrors,

            );

            return $this->HelperFunctions->returnData($returnData, false, "Something Went Wrong (Request Params Failed)", 400);

        }

        $status = DB::insert('insert into employee (name, contact_number,username,password,registration_date,role_id) values (?, ?,?,?, ?,?)',
            [$request->name, $request->contact_number, $request->username, md5($request->password), time(), 2]);


        if ($status == "true") {
            return $this->HelperFunctions->returnData(array(), true, "Employee Registered", 200);
        } else {
            return $this->HelperFunctions->returnData(array(), false, "Something Went Wrong (Employee Registration)", 500);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This method use for update employee
     *
     */
    public function updateEmployee(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'id' => 'string',
            'name' => 'string',
            'contact_number' => 'string',
            'username' => 'string',
            'password' => 'string',

        ]);

        /** Return Response if Validatior Fails */

        if ($validator->fails()) {

            $validationErrors = $validator->errors()->getMessages();

            $returnData = array(

                'message' => $validationErrors,

            );

            return $this->HelperFunctions->returnData($returnData, false, "Something Went Wrong (Request Params Failed)", 400);

        }

        $status = DB::update('update employee set name = ?,contact_number = ?,username = ?,password = ? where id = ?',
            [$request->name, $request->contact_number, $request->username, md5($request->password), $request->id]);


        if ($status != "-1") {
            return $this->HelperFunctions->returnData(array(), true, "Employee Updated", 200);
        } else {
            return $this->HelperFunctions->returnData(array(), false, "Something Went Wrong (Employee Updating)", 500);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This method use for check in employee
     *
     */
    public function checkIN(Request $request)
    {


        $allHeaders = $request->headers->all();
        $token = $allHeaders['session-token'][0];

        $user_id = JwtDecoderHelper::decode($token)['claims']['id'];

        $users_attendance = DB::select('select * from attendance where employee_id = ?', [$user_id]);

        if ($users_attendance == []) {

            $attendance = array(
                "date" => date('d-M-Y', time()),
                "check_in" => date('H:i:s', time()),
                "check_out" => "",
            );

            $status = DB::insert('insert into attendance (attendance, employee_id) values (?, ?)',
                [json_encode(array($attendance)), $user_id]);

            if ($status == "true") {
                return $this->HelperFunctions->returnData(array(), true, "Attendance Recoded, Successfully Check In", 200);
            } else {
                return $this->HelperFunctions->returnData(array(), false, "Something Went Wrong (Attendance Recording)", 500);
            }

        } else {

            $all_attendance_data = json_decode($users_attendance[0]->attendance);

            $last_attendance = end($all_attendance_data);

            if ($last_attendance->date == date('d-M-Y', time())) {
                return $this->HelperFunctions->returnData(array(), false, "Attendance Already Recoded", 208);
            } else {

                $attendance = array(
                    "date" => date('d-M-Y', time()),
                    "check_in" => date('H:i:s', time()),
                    "check_out" => "",
                );

                array_push($all_attendance_data, $attendance);

                $status = DB::update('update attendance set attendance = ? where employee_id = ?',
                    [json_encode($all_attendance_data), $user_id]);


                if ($status == "1") {
                    return $this->HelperFunctions->returnData(array(), true, "Attendance Recoded, Successfully Check In", 200);
                } else {
                    return $this->HelperFunctions->returnData(array(), false, "Something Went Wrong (Attendance Recording)", 500);
                }

            }

        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This method use for check out employee
     *
     */
    public function checkOUT(Request $request)
    {


        $allHeaders = $request->headers->all();
        $token = $allHeaders['session-token'][0];

        $user_id = JwtDecoderHelper::decode($token)['claims']['id'];

        $users_attendance = DB::select('select * from attendance where employee_id = ?', [$user_id]);

        $all_attendance_data = json_decode($users_attendance[0]->attendance);

        $last_attendance = end($all_attendance_data);

        if($last_attendance->check_out != ""){
            return $this->HelperFunctions->returnData(array(), false, "Attendance Already Recoded", 208);
        }

        $attendance = array(
            "date" => $last_attendance->date,
            "check_in" => $last_attendance->check_in,
            "check_out" => date('H:i:s', time()),
        );


        array_pop($all_attendance_data);

        array_push($all_attendance_data, $attendance);

        $status = DB::update('update attendance set attendance = ? where employee_id = ?',
            [json_encode($all_attendance_data), $user_id]);


        if ($status == "1") {
            return $this->HelperFunctions->returnData(array(), true, "Attendance Recoded, Successfully Check Out", 200);
        } else {
            return $this->HelperFunctions->returnData(array(), false, "Something Went Wrong (Attendance Recording)", 500);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This method use for get all employee attendance based on employee id
     *
     */
    public function getAllAttendance(Request $request)
    {


        $allHeaders = $request->headers->all();
        $token = $allHeaders['session-token'][0];

        $user_id = JwtDecoderHelper::decode($token)['claims']['id'];

        $users_attendance = DB::select('select * from attendance where employee_id = ? order by id desc', [$user_id]);

        if ($users_attendance == []) {
            return $this->HelperFunctions->returnData(array(), false, "No Data Found", 404);
        }

        $all_attendance_data = json_decode($users_attendance[0]->attendance);

        return $this->HelperFunctions->returnData($all_attendance_data, true, "Attendance Data", 200);


    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This method use for search employee by mobile number
     *
     */
    public function searchEmployee(Request $request){

        $users_details = DB::select('select id,name,contact_number,username,password from employee where contact_number = ? order by id desc', [$request->mobile]);

        if ($users_details == []) {
            return $this->HelperFunctions->returnData(array(), false, "No Data Found", 404);
        }

        return $this->HelperFunctions->returnData($users_details[0], true, "User Details", 200);


    }

}
