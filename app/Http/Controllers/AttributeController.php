<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attribute;
use Validator;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::all();
        return response()->json($attributes);
    }

    public function show($id)
    {
        $attribute = Attribute::find($id);
        if (!$attribute) {
            return response()->json(['error' => 'Attribute not found'], 404);
        }
        return response()->json($attribute);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'type' => 'required|in:text,date,number,select'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $attribute = Attribute::create($request->only('name', 'type'));
        return response()->json($attribute, 201);
    }

    public function update(Request $request, $id)
    {
        $attribute = Attribute::find($id);
        if (!$attribute) {
            return response()->json(['error' => 'Attribute not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:text,date,number,select'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $attribute->update($request->only('name', 'type'));
        return response()->json($attribute);
    }

    public function destroy($id)
    {
        $attribute = Attribute::find($id);
        if (!$attribute) {
            return response()->json(['error' => 'Attribute not found'], 404);
        }
        $attribute->delete();
        return response()->json(['message' => 'Attribute deleted successfully']);
    }
}
