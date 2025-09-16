<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function createTask(Request $request){

        $validator =Validator::make($request->all(),[
            'title' => 'required',
            'assignee' => '',
            'due_date' => 'required',
            'time_tracked' => '',
            'status' => '',
            'priority' => '',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $task = new Task();

        $due_date = Carbon::parse($validated['due_date']);

        if ($due_date->lt(Carbon::now())) {
            return response()->json([
                "success" => false,
                "message" => "due_date cannot be in the past!",
                "data" => (object)[]
            ],400);
        } 

        $task->title = $validated['title'];
        $task->assignee = $validated['assignee'] ?: '';
        $task->due_date = $due_date;
        $task->time_tracked = $validated['time_tracked'] ?: 0;
        $task->status = $validated['status'] ?: 'pending';
        $task->priority = $validated['priority'];

        if($task->save()){
            return response()->json([
                "success" => true,
                "message" => "Task created successfully",
                "data" => $task
            ],200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Create task failed!",
                "data" => (object)[]
            ],400);
        }
    }
}
