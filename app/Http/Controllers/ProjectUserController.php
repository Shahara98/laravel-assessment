<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use Validator;

class ProjectUserController extends Controller
{

    public function addUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $project = Project::find($id);
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $project->users()->syncWithoutDetaching($request->input('user_id'));

        return response()->json(['message' => 'User added to project successfully'], 200);
    }
}
