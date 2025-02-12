<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\AttributeValue;
use Validator;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query();

        $filters = $request->query('filters', []);

        $projectColumns = ['name', 'status', 'created_at', 'updated_at'];

        foreach ($filters as $key => $rawValue) {
            $operator = '=';
            $value = $rawValue;

            if (strpos($rawValue, ':') !== false) {
                list($op, $val) = explode(':', $rawValue, 2);
                $op = strtolower($op);
                if (in_array($op, ['=', '>', '<', 'like'])) {
                    $operator = ($op === 'like') ? 'LIKE' : $op;
                    $value = $val;
                }
            }

            if (in_array($key, $projectColumns)) {
                $query->where($key, $operator, $value);
            } else {
                $query->whereHas('attributeValues', function ($q) use ($key, $operator, $value) {
                    $q->join('attributes', 'attributes.id', '=', 'attribute_values.attribute_id')
                      ->where('attributes.name', $key)
                      ->where('attribute_values.value', $operator, $value);
                });
            }
        }

        $projects = $query->with('attributeValues.attribute')->get();

        return response()->json($projects);
    }

    public function show($id)
    {
        $project = Project::with('attributeValues.attribute')->find($id);
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }
        return response()->json($project);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $project = Project::create($request->only('name', 'status'));

        $attributes = $request->input('attributes');
        if ($attributes && is_array($attributes)) {
            foreach ($attributes as $attribute_id => $value) {
                AttributeValue::create([
                    'attribute_id' => $attribute_id,
                    'entity_id'    => $project->id,
                    'value'        => $value
                ]);
            }
        }
        $project->load('attributeValues.attribute');
        return response()->json($project, 201);
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'   => 'sometimes|required|string',
            'status' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $project->update($request->only('name', 'status'));

        $attributes = $request->input('attributes');

        \Log::info('Update Attributes:', is_array($attributes) ? $attributes : ['attributes' => $attributes]);

        if ($attributes && is_array($attributes)) {
            foreach ($attributes as $attribute_id => $value) {
                AttributeValue::updateOrCreate(
                    [
                        'attribute_id' => $attribute_id,
                        'entity_id'    => $project->id,
                    ],
                    ['value' => $value]
                );
            }
        }

        $project->load('attributeValues.attribute');

        return response()->json($project);
    }

    public function destroy($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }
        $project->delete();
        return response()->json(['message' => 'Project deleted successfully']);
    }

    // public function filter(Request $request)
    // {
    //     $attribute_id = $request->query('attribute_id');
    //     $value = $request->query('value');

    //     if (!$attribute_id || !$value) {
    //         return response()->json(['error' => 'attribute_id and value are required for filtering'], 422);
    //     }

    //     $projects = Project::whereHas('attributeValues', function($query) use ($attribute_id, $value) {
    //         $query->where('attribute_id', $attribute_id)
    //               ->where('value', $value);
    //     })->with('attributeValues.attribute')->get();

    //     return response()->json($projects);
    // }

}
