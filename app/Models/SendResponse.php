<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendResponse extends Model
{
    use HasFactory;

    public static function successResponse($result)
    {
        return response()->json([
            'status' => true,
            'result' => $result,
        ]);
    }

    // public static function __callStatic($name, $arguments)
    // {
    //     if ($name == 'DatabaseError') {
    //         return self::errorResponse(5, 'sql error');
    //     } else if ($name == 'ParamError') {
    //         $result = implode(' , ', $arguments[0]);
    //         return self::errorResponse(27, $result . ' has wrong data');
    //     }
    // }

    public static function errorResponse($error_code, $result)
    {
        return response()->json([
            'status' => false,
            'error_code' => $error_code,
            'result' => $result
        ]);
    }

    public static function newException($message, $code)
    {
        throw new \Exception($message, $code);
    }
}
