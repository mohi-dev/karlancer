<?php

namespace App\Http\Controllers;

use App\Models\SendResponse;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response as HttpResponse;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|min:8'
        ]);
        if ($validated->fails()) {
            return SendResponse::errorResponse(HttpResponse::HTTP_BAD_REQUEST, $validated->errors()->first());
        }
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        try {
            $saved = $user->save();
        } catch (Exception $exception) {
            $error = $exception->getCode();
            if ($error == 1062) {
                return SendResponse::errorResponse(HttpResponse::HTTP_BAD_REQUEST, 'Duplicate Error');
            }
            return SendResponse::errorResponse($exception->getCode(), $exception->getMessage());
        }
        if (!$saved) {
            return SendResponse::DatabaseError();
        }
        return SendResponse::successResponse('user created');
    }

    public function login(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'email'=>'required|string|email',
            'password'=>'required|min:8'
        ]);
        if ($validated->fails()) {
            return SendResponse::errorResponse(HttpResponse::HTTP_BAD_REQUEST, $validated->errors()->first());
        }
        $user = User::where('email', $request->email)->first();
        if (is_null($user) or !Hash::check($request->password,$user->password)) {
            return SendResponse::errorResponse(HttpResponse::HTTP_UNAUTHORIZED, 'user not found');
        }
        try {
            $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;
        } catch (Exception $exception) {
            return SendResponse::errorResponse($exception->getCode(), $exception->getMessage());
        }
        return SendResponse::successResponse(['token :' => $token, 'activation link :' => "http://127.0.0.1:8000/api/user/active/"]);
    }

    public function logout (Request $request)
    {
        if (!$request->user('sanctum')) {
            return SendResponse::errorResponse(HttpResponse::HTTP_FORBIDDEN, 'login first');
        }
        auth()->user()->tokens()->delete();
        return SendResponse::successResponse('you have been loged out');
    }

    public function active (Request $request)
    {
        if (!$request->user('sanctum')) {
            return SendResponse::errorResponse(HttpResponse::HTTP_FORBIDDEN, 'login first');
        }
        $user = $request->user('sanctum');
        $user->active = 1;
        try {
            $saved = $user->save();
        } catch (Exception $exception) {
            return SendResponse::errorResponse($exception->getCode(), $exception->getMessage());
        }
        if (!$saved) {
            return SendResponse::DatabaseError();
        }
        return SendResponse::successResponse('user activated');
    }
}
