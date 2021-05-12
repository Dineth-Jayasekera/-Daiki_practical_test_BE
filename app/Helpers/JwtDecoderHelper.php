<?php


namespace App\Helpers;


class JwtDecoderHelper
{
    public static function decode($jwt = null)
    {
        $jwt = $jwt ?? \Auth::getToken();

        if ($jwt) {
            $jwt = list($header, $claims, $signature) = explode('.', $jwt);

            $header = self::decodeFragment($header);
            $claims = self::decodeFragment($claims);
            $signature = (string)base64_decode($signature);

            return [
                'header' => $header,
                'claims' => $claims,
                'signature' => $signature
            ];
        }

        return false;
    }

    protected static function decodeFragment($value)
    {
        return (array)json_decode(base64_decode($value));
    }
}
