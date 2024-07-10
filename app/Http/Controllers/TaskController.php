<?php

namespace App\Http\Controllers;
use App\Models\SendResponse;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response as HttpResponse;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function create(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'title'=>'required',
        ]);
        if ($validated->fails()) {
            return SendResponse::errorResponse(HttpResponse::HTTP_BAD_REQUEST, $validated->errors()->first());
        }
        $user = $request->user('sanctum');
        if ($user->active == 0) {
            return SendResponse::errorResponse(HttpResponse::HTTP_FORBIDDEN, 'please active your profile first');
        }
        $task = new Task();
        $task->title = $request->title;
        $task->user_id = $user->id;
        try {
            $saved = $task->save();
        } catch (Exception $exception) {
            return SendResponse::errorResponse($exception->getCode(), $exception->getMessage());
        }
        if (!$saved) {
            return SendResponse::DatabaseError();
        }
        return SendResponse::successResponse('Title Created');
    }

    public function update(Request $request, $id)
    {
        $user = $request->user('sanctum');
        if ($user->active == 0) {
            return SendResponse::errorResponse(HttpResponse::HTTP_FORBIDDEN, 'please active your profile first');
        }
        $task = Task::where('id', '=', $id)->where('user_id', '=', $user->id)->first();
        if (is_null($task)) {
            return SendResponse::errorResponse(HttpResponse::HTTP_NOT_FOUND, 'Task Not Found');
        }
        $items = ['title'];
        foreach ($request->all() as $param => $val) {
            if (in_array($param, $items)) {
                $task->{$param} = $val;
            }
        }
        try {
            $saved = $task->save();
        } catch (Exception $exception) {
            return SendResponse::errorResponse($exception->getCode(), $exception->getMessage());
        }
        if (!$saved) {
            return SendResponse::DatabaseError();
        }
        return SendResponse::successResponse($task);
    }

    public function delete(Request $request, $id)
    {
        $user = $request->user('sanctum');
        if ($user->active == 0) {
            return SendResponse::errorResponse(HttpResponse::HTTP_FORBIDDEN, 'please active your profile first');
        }
        $task = Task::where('id', '=', $id)->where('user_id', '=', $user->id)->first();
        if (is_null($task)) {
            return SendResponse::errorResponse(HttpResponse::HTTP_NOT_FOUND, 'Task Not Found');
        }
        try {
            $deleted = $task->delete();
        } catch (Exception $exception) {
            return SendResponse::errorResponse($exception->getCode(), $exception->getMessage());
        }
        if (!$deleted) {
            return SendResponse::DatabaseError();
        }
        return SendResponse::successResponse('Task Deleted !');
    }

    public function list(Request $request) 
    {
        $user = $request->user('sanctum');
        if ($user->active == 0) {
            return SendResponse::errorResponse(HttpResponse::HTTP_FORBIDDEN, 'please active your profile first');
        }
        $task = Task::where('user_id', '=', $user->id);
        return SendResponse::successResponse($task->paginate(10));
    }
}
