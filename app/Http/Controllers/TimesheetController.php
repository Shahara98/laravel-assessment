<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timesheet;
use App\Models\User;
use App\Models\Project;
use Validator;

class TimesheetController extends Controller
{
    public function index()
    {
        $timesheets = Timesheet::with(['user', 'project'])->get();
        return response()->json($timesheets);
    }

    public function show($id)
    {
        $timesheet = Timesheet::with(['user', 'project'])->find($id);
        if (!$timesheet) {
            return response()->json(['error' => 'Timesheet not found'], 404);
        }
        return response()->json($timesheet);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_name'  => 'required|string',
            'date'       => 'required|date',
            'hours'      => 'required|numeric',
            'user_id'    => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $userId = $request->input('user_id');
        $projectId = $request->input('project_id');

        $project = Project::find($projectId);
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        if (!$project->users()->where('user_id', $userId)->exists()) {
            return response()->json(['error' => 'User not assigned to the project'], 403);
        }

        $timesheet = Timesheet::create($request->all());
        return response()->json($timesheet, 201);
    }

    public function update(Request $request, $id)
    {
        $timesheet = Timesheet::find($id);
        if (!$timesheet) {
            return response()->json(['error' => 'Timesheet not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'task_name' => 'sometimes|required|string',
            'date'      => 'sometimes|required|date',
            'hours'     => 'sometimes|required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $timesheet->update($request->all());
        return response()->json($timesheet);
    }

    public function destroy($id)
    {
        $timesheet = Timesheet::find($id);
        if (!$timesheet) {
            return response()->json(['error' => 'Timesheet not found'], 404);
        }
        $timesheet->delete();
        return response()->json(['message' => 'Timesheet deleted successfully']);
    }
}
