<?php


namespace App\Helpers;


class HelperFunctions
{

    function returnData($data, $success = true, $message = '', $code = 200)
    {

        $session_token = session('session_token', null);
        if ($success) {
            if ($message == '') {
                $message = 'Success';
            }
        } else {
            if ($message == '') {
                $message = 'Error';
            }
        }

        return response()->json([
            'session_token' => $session_token,
            'success' => $success,
            'message' => $message,
            'code' => $code,
            'data' => $data
        ],$code);
    }



}
