<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Exports\TaskExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
        $task->time_tracked = $validated['time_tracked'] ?? 0;
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

    public function getTask(Request $request){
        $title = $request->query('title',null);
        $assignee = $request->query('assignee',null);
        $start_due_date = $request->query('start_due_date',null);
        $end_due_date = $request->query('end_due_date',null);
        $min_time_tracked = $request->query('min_time_tracked',null);
        $max_time_tracked = $request->query('max_time_tracked',null);
        $status = $request->query('status',null);
        $priority = $request->query('priority',null);

        return Excel::download(new TaskExport($title,$assignee,$start_due_date,$end_due_date,$min_time_tracked,$max_time_tracked,$status,$priority), 'tasks.xlsx');
    }

    public function getCartTask(Request $request){
        $type = $request->query('type','');
        if($type == 'status'){

            $pendingTask = Task::where('status','=','pending')->count();
            $openTask = Task::where('status','=','open')->count();
            $in_progressTask = Task::where('status','=','in_progress')->count();
            $completedTask = Task::where('status','=','completed')->count();

            return response()->json([
                "status_summary" => [
                    "pending" =>  $pendingTask,
                    "open" => $openTask,
                    "in_progress" => $in_progressTask,
                    "completed" => $completedTask
                ]
            ]);

        } else if($type == 'priority'){
            $low = Task::where('priority','=','low')->count();
            $medium = Task::where('priority','=','medium')->count();
            $high = Task::where('priority','=','high')->count();

            return response()->json([
                "priority_summary" => [
                    "low" =>  $low,
                    "medium" => $medium,
                    "high" => $high,
                ]
            ]);
        } else if($type == 'assignee'){
            $assigneeSummary = [];

            $dataAssignee = Task::pluck('assignee');

            foreach ($dataAssignee as $key => $value) {
                $item = [
                    $value => [
                        'total_todos' => Task::where('assignee','=',$value)->count(),
                        'total_pending_todos' => Task::where('assignee','=',$value)->where('status','=','pending')->count(),
                        'total_timetracked_completed_todos' => Task::where('assignee','=',$value)->sum('time_tracked'),
                    ]
                ];
                
                $assigneeSummary = array_merge($assigneeSummary,$item);
            }

            return response()->json([
                "assignee_summary" => $assigneeSummary
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => "request query type required!",
                "data" => (object)[]
            ]);
        }
    }
}
